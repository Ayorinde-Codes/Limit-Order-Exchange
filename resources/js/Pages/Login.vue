<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow">
            <h2 class="text-2xl font-bold text-center">Login</h2>
            <div v-if="errors.message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ errors.message }}
            </div>
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                        />
                        <div v-if="errors.email" class="text-red-600 text-sm mt-1">{{ errors.email }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input 
                            v-model="form.password" 
                            type="password" 
                            required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                        />
                        <div v-if="errors.password" class="text-red-600 text-sm mt-1">{{ errors.password }}</div>
                    </div>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Logging in...' : 'Login' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const submit = () => {
    form.post('/login', {
        preserveScroll: true,
        onSuccess: () => {
        },
        onError: (errors) => {
            console.error('Login errors:', errors);
        },
    });
};
</script>

