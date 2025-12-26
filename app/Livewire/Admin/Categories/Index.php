<?php

namespace App\Livewire\Admin\Categories;

use App\Models\CategorySermon;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $categoryId = null;
    public $name = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:category_sermons,name',
    ];

    public function mount() {}

    public function render()
    {
        $categories = CategorySermon::query()
            ->withCount(['sermons' => function ($q) {
                $q->where('is_published', true);
            }])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $category = CategorySermon::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->categoryId) {
            $this->validate([
                'name' => 'required|string|max:255|unique:category_sermons,name,' . $this->categoryId,
            ]);
            
            $category = CategorySermon::findOrFail($this->categoryId);
            $category->update(['name' => $this->name]);
            
            session()->flash('message', 'Catégorie mise à jour avec succès.');
        } else {
            $this->validate();
            
            CategorySermon::create(['name' => $this->name]);
            
            session()->flash('message', 'Catégorie créée avec succès.');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($id)
    {
        $category = CategorySermon::findOrFail($id);
        
        if ($category->sermons()->count() > 0) {
            session()->flash('error', 'Impossible de supprimer une catégorie qui contient des sermons.');
            return;
        }
        
        $category->delete();
        session()->flash('message', 'Catégorie supprimée avec succès.');
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    private function resetForm()
    {
        $this->categoryId = null;
        $this->name = '';
        $this->resetValidation();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
