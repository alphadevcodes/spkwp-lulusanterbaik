<?php

use App\Enums\CriteriaAttribute;
use App\Enums\CriteriaCategory;
use App\Models\Criteria;
use App\Services\CriteriaService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

return new
#[Title('Manajemen Kriteria')]
class extends Component
{
    use WithPagination;

    // ----- State: tabel (search, filter, sort) -----
    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $category = '';

    #[Url(history: true)]
    public string $attribute = '';

    public string $sortField = 'code';

    public string $sortDirection = 'asc';

    public int $perPage = 10;

    // ----- State: modal form (create/edit) -----
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    public array $form = [
        'code' => '',
        'name' => '',
        'category' => '',
        'attribute' => '',
        'weight' => 0,
    ];

    // ----- State: konfirmasi hapus -----
    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public function mount(): void
    {
        $this->form['category'] = CriteriaCategory::UTAMA->value;
        $this->form['attribute'] = CriteriaAttribute::BENEFIT->value;
    }

    /**
     * Reset pagination tiap kali search/filter berubah.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedAttribute(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Buka modal untuk create baru.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->editingId = null;
        $this->showModal = true;
    }

    /**
     * Buka modal untuk edit data yang sudah ada.
     */
    public function edit(int $id): void
    {
        $criteria = Criteria::findOrFail($id);

        $this->form = [
            'code' => $criteria->code,
            'name' => $criteria->name,
            'category' => $criteria->category,
            'attribute' => $criteria->attribute,
            'weight' => $criteria->weight,
        ];

        $this->isEditing = true;
        $this->editingId = $criteria->id;
        $this->showModal = true;
    }

    /**
     * Simpan form (create atau update tergantung state isEditing).
     */
    public function save(CriteriaService $service): void
    {
        $this->validate(
            $service->rules($this->editingId),
            attributes: $service->validationAttributes()
        );

        if ($this->isEditing && $this->editingId) {
            $criteria = Criteria::findOrFail($this->editingId);
            $service->update($criteria, $this->form);
            Flux::toast(
                heading: 'Berhasil diperbarui',
                text: "Kriteria \"{$this->form['name']}\" telah diperbarui.",
                variant: 'success',
            );
        } else {
            $service->create($this->form);
            Flux::toast(
                heading: 'Berhasil ditambahkan',
                text: "Kriteria \"{$this->form['name']}\" telah ditambahkan.",
                variant: 'success',
            );
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();

        $this->warnIfWeightNotComplete($service);
    }

    /**
     * Tampilkan toast peringatan jika total bobot seluruh kriteria belum 100%.
     * Penting untuk SPK karena perhitungan (mis. SAW) mengasumsikan total bobot = 100%.
     */
    protected function warnIfWeightNotComplete(CriteriaService $service): void
    {
        $total = $service->totalWeight();

        if ($total !== 100) {
            Flux::toast(
                heading: 'Perhatian',
                text: "Total bobot saat ini {$total}%. Pastikan total bobot mencapai 100% sebelum melakukan perhitungan.",
                variant: 'warning',
                duration: 6000,
            );
        }
    }

    /**
     * Buka modal konfirmasi hapus.
     */
    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Eksekusi hapus setelah dikonfirmasi.
     */
    public function delete(CriteriaService $service): void
    {
        if (! $this->deletingId) {
            return;
        }

        $criteria = Criteria::findOrFail($this->deletingId);
        $criteriaName = $criteria->name;
        $service->delete($criteria);

        Flux::toast(
            heading: 'Berhasil dihapus',
            text: "Kriteria \"{$criteriaName}\" telah dihapus.",
            variant: 'success',
        );

        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->resetPage();

        $this->warnIfWeightNotComplete($service);
    }

    public function resetForm(): void
    {
        $this->form = [
            'code' => '',
            'name' => '',
            'category' => CriteriaCategory::UTAMA->value,
            'attribute' => CriteriaAttribute::BENEFIT->value,
            'weight' => 0,
        ];
        $this->resetErrorBag();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'category', 'attribute');
        $this->resetPage();
    }

    #[Computed]
    public function categoryOptions(): array
    {
        return CriteriaCategory::options();
    }

    #[Computed]
    public function attributeOptions(): array
    {
        return CriteriaAttribute::options();
    }

    #[Computed]
    public function totalWeight(): int
    {
        return app(CriteriaService::class)->totalWeight();
    }

    public function with(CriteriaService $service): array
    {
        return [
            'criterias' => $service->paginate([
                'search' => $this->search,
                'category' => $this->category,
                'attribute' => $this->attribute,
                'sortField' => $this->sortField,
                'sortDirection' => $this->sortDirection,
            ], $this->perPage),
        ];
    }
};
