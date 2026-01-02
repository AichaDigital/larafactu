<div class="dropdown dropdown-end" x-data="{ open: false }">
    <div tabindex="0" role="button" class="btn btn-ghost btn-circle" @click="open = !open">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
        </svg>
    </div>
    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-40 p-2 shadow" x-show="open" @click.away="open = false">
        <li class="menu-title">Tema</li>
        <li>
            <button onclick="document.documentElement.setAttribute('data-theme', 'cupcake'); fetch('/api/theme', {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}, body: JSON.stringify({theme: 'cupcake'})})">
                <span class="badge badge-sm bg-pink-200"></span>
                Cupcake
            </button>
        </li>
        <li>
            <button onclick="document.documentElement.setAttribute('data-theme', 'corporate'); fetch('/api/theme', {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}, body: JSON.stringify({theme: 'corporate'})})">
                <span class="badge badge-sm bg-blue-200"></span>
                Corporate
            </button>
        </li>
        <li>
            <button onclick="document.documentElement.setAttribute('data-theme', 'abyss'); fetch('/api/theme', {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}, body: JSON.stringify({theme: 'abyss'})})">
                <span class="badge badge-sm bg-indigo-900"></span>
                Abyss
            </button>
        </li>
        <li>
            <button onclick="document.documentElement.setAttribute('data-theme', 'sunset'); fetch('/api/theme', {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}, body: JSON.stringify({theme: 'sunset'})})">
                <span class="badge badge-sm bg-orange-700"></span>
                Sunset
            </button>
        </li>
    </ul>
</div>
