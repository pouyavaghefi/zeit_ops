{{-- Replace the existing Infrastructure SSH Keys & Access href="#" item with this link. --}}
<li>
    <a href="{{ route('panel.servers.ssh-keys.select') }}"
       class="{{ request()->routeIs('panel.servers.ssh-keys.*') ? 'active' : '' }}">
        <i class="fas fa-key"></i> SSH Keys &amp; Access
    </a>
</li>
