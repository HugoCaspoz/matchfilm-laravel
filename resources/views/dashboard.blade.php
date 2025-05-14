<x-nav-link :href="route('movies.index')" :active="request()->routeIs('movies.index')">
    {{ __('Películas') }}
</x-nav-link>

<x-nav-link :href="route('matches.index')" :active="request()->routeIs('matches.index')">
    {{ __('Mis Películas') }}
</x-nav-link>

<x-nav-link :href="route('preferences.edit')" :active="request()->routeIs('preferences.edit')">
    {{ __('Preferencias') }}
</x-nav-link>