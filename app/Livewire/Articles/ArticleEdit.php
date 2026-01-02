<?php

declare(strict_types=1);

namespace App\Livewire\Articles;

use AichaDigital\Larabill\Enums\ItemType;
use AichaDigital\Larabill\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Editar Articulo')]
class ArticleEdit extends Component
{
    public Article $article;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    public int $itemType = 1;

    public string $category = '';

    public int $costPrice = 0;

    public bool $isActive = true;

    // Base price
    public int $basePrice = 0;

    public int $taxRate = 21;

    public ?int $basePriceId = null;

    public function mount(Article $article): void
    {
        $this->article = $article;

        $this->code = $article->code;
        $this->name = $article->getTranslation('name', 'es') ?: (is_array($article->name) ? ($article->name['es'] ?? '') : $article->name);
        $this->description = $article->getTranslation('description', 'es') ?: (is_array($article->description) ? ($article->description['es'] ?? '') : ($article->description ?? ''));
        $this->itemType = $article->item_type instanceof ItemType ? $article->item_type->value : $article->item_type;
        $this->category = $article->category ?? '';
        $this->costPrice = $article->cost_price ?? 0;
        $this->isActive = $article->is_active;

        // Load base price (ONE_TIME)
        $basePrice = DB::table('article_prices')
            ->where('article_id', $article->id)
            ->where('billing_frequency', 0) // ONE_TIME
            ->where('is_active', true)
            ->first();

        if ($basePrice) {
            $this->basePriceId = $basePrice->id;
            $this->basePrice = $basePrice->price ?? 0;
            $this->taxRate = $basePrice->tax_rate ?? 21;
        }
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('articles', 'code')->ignore($this->article->id),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'itemType' => 'required|integer|in:0,1',
            'category' => 'nullable|string|max:100',
            'costPrice' => 'nullable|integer|min:0',
            'isActive' => 'boolean',
            'basePrice' => 'required|integer|min:0',
            'taxRate' => 'required|integer|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El codigo es obligatorio.',
            'code.unique' => 'Este codigo ya existe.',
            'name.required' => 'El nombre es obligatorio.',
            'basePrice.required' => 'El precio es obligatorio.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            // Update article
            $this->article->update([
                'code' => strtoupper($this->code),
                'name' => ['es' => $this->name],
                'description' => $this->description ? ['es' => $this->description] : null,
                'item_type' => ItemType::from($this->itemType),
                'category' => $this->category ?: null,
                'cost_price' => $this->costPrice ?: null,
                'is_active' => $this->isActive,
            ]);

            // Update or create base price
            if ($this->basePriceId) {
                DB::table('article_prices')
                    ->where('id', $this->basePriceId)
                    ->update([
                        'price' => $this->basePrice,
                        'tax_rate' => $this->taxRate,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('article_prices')->insert([
                    'article_id' => $this->article->id,
                    'billing_frequency' => 0, // ONE_TIME
                    'price' => $this->basePrice,
                    'tax_rate' => $this->taxRate,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        session()->flash('success', 'Articulo actualizado correctamente.');

        $this->redirect(route('articles.index'), navigate: true);
    }

    public function render(): View
    {
        $existingCategories = DB::table('articles')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        return view('livewire.articles.article-edit', [
            'existingCategories' => $existingCategories,
        ]);
    }
}
