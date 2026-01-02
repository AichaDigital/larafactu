<?php

declare(strict_types=1);

namespace App\Livewire\Articles;

use AichaDigital\Larabill\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Articulos')]
class ArticleList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $status = '';

    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $article = Article::find($id);

        if (! $article) {
            session()->flash('error', 'Articulo no encontrado.');

            return;
        }

        // Check if article is used in invoices
        $hasInvoiceItems = DB::table('invoice_items')
            ->where('article_id', $id)
            ->exists();

        if ($hasInvoiceItems) {
            session()->flash('error', 'No se puede eliminar un articulo usado en facturas.');

            return;
        }

        $article->delete();

        session()->flash('success', 'Articulo eliminado correctamente.');
    }

    public function toggleActive(int $id): void
    {
        $article = Article::find($id);

        if ($article) {
            $article->update(['is_active' => ! $article->is_active]);
            session()->flash('success', $article->is_active ? 'Articulo activado.' : 'Articulo desactivado.');
        }
    }

    public function render(): View
    {
        $query = Article::query();

        // Search filter
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($this->type !== '') {
            $query->where('item_type', (int) $this->type);
        }

        // Category filter
        if ($this->category !== '') {
            $query->where('category', $this->category);
        }

        // Status filter
        if ($this->status !== '') {
            $query->where('is_active', $this->status === '1');
        }

        $articles = $query
            ->orderBy('code')
            ->paginate($this->perPage);

        // Get categories for filter
        $categories = DB::table('articles')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        return view('livewire.articles.article-list', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }
}
