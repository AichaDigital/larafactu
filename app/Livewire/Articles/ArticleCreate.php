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
#[Title('Nuevo Articulo')]
class ArticleCreate extends Component
{
    public string $code = '';

    public string $name = '';

    public string $description = '';

    public int $itemType = 1; // SERVICE by default

    public string $category = '';

    public int $costPrice = 0; // in cents

    public bool $isActive = true;

    // Base price (simplified - single price)
    public int $basePrice = 0; // in cents

    public int $taxRate = 21;

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('articles', 'code'),
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
            // Create article with translatable name/description
            $article = Article::create([
                'code' => strtoupper($this->code),
                'name' => ['es' => $this->name],
                'description' => $this->description ? ['es' => $this->description] : null,
                'item_type' => ItemType::from($this->itemType),
                'category' => $this->category ?: null,
                'cost_price' => $this->costPrice ?: null,
                'is_active' => $this->isActive,
            ]);

            // Create base price (ONE_TIME = 0)
            DB::table('article_prices')->insert([
                'article_id' => $article->id,
                'billing_frequency' => 0, // ONE_TIME
                'price' => $this->basePrice,
                'tax_rate' => $this->taxRate,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        session()->flash('success', 'Articulo creado correctamente.');

        $this->redirect(route('articles.index'), navigate: true);
    }

    public function generateCode(): void
    {
        if ($this->name && empty($this->code)) {
            // Generate code from name
            $baseCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $this->name), 0, 6));
            $suffix = 1;
            $code = $baseCode;

            while (Article::where('code', $code)->exists()) {
                $code = $baseCode.str_pad((string) $suffix, 2, '0', STR_PAD_LEFT);
                $suffix++;
            }

            $this->code = $code;
        }
    }

    public function render(): View
    {
        // Get existing categories for suggestions
        $existingCategories = DB::table('articles')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        return view('livewire.articles.article-create', [
            'existingCategories' => $existingCategories,
        ]);
    }
}
