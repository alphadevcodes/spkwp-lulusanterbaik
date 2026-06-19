{{--
    Komponen notifikasi global (toast) menggunakan Flux UI.

    Cara pakai:
    1. Include partial ini SEKALI di layout utama (app.blade.php / auth.blade.php),
       biasanya tepat sebelum penutup </body>.

       Contoh:
           @include('livewire.components.notification')

    2. Trigger toast dari mana saja di Livewire component (termasuk Volt) dengan:

           use Flux\Flux;

           Flux::toast(text: 'Data berhasil disimpan.', variant: 'success');
           Flux::toast(heading: 'Gagal!', text: 'Terjadi kesalahan.', variant: 'danger');
           Flux::toast(text: 'Periksa kembali data Anda.', variant: 'warning');

    Variant yang tersedia: success, warning, danger (default: tanpa variant = netral/info).
--}}
@persist('toast')
    <flux:toast.group position="top end">
        <flux:toast />
    </flux:toast.group>
@endpersist
