<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold">Limit Order Exchange</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span v-if="$page.props.auth?.user">{{ $page.props.auth.user.name }}</span>
                        <Link 
                            v-if="$page.props.auth?.user" 
                            href="/logout" 
                            method="post" 
                            as="button"
                            class="text-red-600 hover:text-red-800"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </nav>
        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

