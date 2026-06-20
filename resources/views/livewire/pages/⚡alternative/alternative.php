<?php

use App\Models\Alternative;
use App\Services\AlternativeService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

return new
#[Title('Manajemen Alternatif')]
class extends Component
{
    use WithPagination;

    // ----- State: tabel (search, sort) -----
    #[Url(as: 'q', history: true)]
    public string $search = '';

    public string $sortField = 'code';

    public string $sortDirection = 'asc';

    public int $perPage = 10;

    // ----- State: modal form (create/edit) -----
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    public array $form = [
        'code' => '',
        'student_name' => '',
        'values' => [],
    ];

    // ----- State: konfirmasi hapus -----
    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    /**
     * Reset pagination tiap kali search berubah.
     */
    public function updatedSearch(): void
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
     * Buka modal untuk edit data yang sudah ada, termasuk nilai per criteria-nya.
     */
    public function edit(int $id, AlternativeService $service): void
    {
        $alternative = Alternative::findOrFail($id);

        $this->form = [
            'code' => $alternative->code,
            'student_name' => $alternative->student_name,
            'values' => $service->valuesForForm($alternative),
        ];

        $this->isEditing = true;
        $this->editingId = $alternative->id;
        $this->showModal = true;
    }

    /**
     * Simpan form (create atau update tergantung state isEditing).
     * Service yang menjamin alternative_values tetap sinkron dengan form.values
     * (upsert nilai yang dikirim, hapus nilai criteria yang dikosongkan).
     */
    public function save(AlternativeService $service): void
    {
        $this->validate(
            $service->rules($this->editingId),
            attributes: $service->validationAttributes()
        );

        $data = [
            'code' => $this->form['code'],
            'student_name' => $this->form['student_name'],
        ];

        if ($this->isEditing && $this->editingId) {
            $alternative = Alternative::findOrFail($this->editingId);
            $service->update($alternative, $data, $this->form['values']);
            Flux::toast(
                heading: 'Berhasil diperbarui',
                text: "Alternatif \"{$this->form['student_name']}\" telah diperbarui.",
                variant: 'success',
            );
        } else {
            $service->create($data, $this->form['values']);
            Flux::toast(
                heading: 'Berhasil ditambahkan',
                text: "Alternatif \"{$this->form['student_name']}\" telah ditambahkan.",
                variant: 'success',
            );
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
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
     * Eksekusi hapus setelah dikonfirmasi. Baris alternative_values terkait
     * ikut terhapus otomatis lewat cascadeOnDelete(), tidak ada baris yatim.
     */
    public function delete(AlternativeService $service): void
    {
        if (! $this->deletingId) {
            return;
        }

        $alternative = Alternative::findOrFail($this->deletingId);
        $studentName = $alternative->student_name;
        $service->delete($alternative);

        Flux::toast(
            heading: 'Berhasil dihapus',
            text: "Alternatif \"{$studentName}\" telah dihapus.",
            variant: 'success',
        );

        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->form = [
            'code' => '',
            'student_name' => '',
            'values' => [],
        ];
        $this->resetErrorBag();
    }

    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    #[Computed]
    public function criteriaColumns(): \Illuminate\Support\Collection
    {
        return app(AlternativeService::class)->criteriaColumns();
    }

    #[Computed]
    public function hasIncompleteValues(): bool
    {
        return app(AlternativeService::class)->hasIncompleteValues();
    }

    public function with(AlternativeService $service): array
    {
        return [
            'alternatives' => $service->paginate([
                'search' => $this->search,
                'sortField' => $this->sortField,
                'sortDirection' => $this->sortDirection,
            ], $this->perPage),
        ];
    }
};
?>