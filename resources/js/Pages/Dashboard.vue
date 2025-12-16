<template>
    <Layout>
        <!-- Toast Notifications -->
        <div v-if="toast.show" :class="toastClass" class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm">
            <div class="flex items-center justify-between">
                <span>{{ toast.message }}</span>
                <button @click="toast.show = false" class="ml-4 text-white hover:text-gray-200">×</button>
            </div>
        </div>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Wallet view -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Wallet</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between py-2 border-b">
                            <span class="font-medium">Balance:</span>
                            <span class="font-bold">${{ formatNumber(profile.balance) }}</span>
                        </div>
                        <div v-if="profile.assets && profile.assets.length > 0">
                            <div v-for="asset in profile.assets" :key="asset.symbol" class="flex justify-between py-2 border-b">
                                <span class="font-medium">{{ asset.symbol }}:</span>
                                <span>{{ formatNumber(asset.amount) }} <span class="text-gray-500">(Available: {{ formatNumber(asset.available) }})</span></span>
                            </div>
                        </div>
                        <div v-else class="text-gray-500 py-2">No assets</div>
                    </div>
                </div>

                <!-- Limit Order Form -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Place Order</h2>
                    <form @submit.prevent="submit">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Symbol</label>
                                <select v-model="form.symbol" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="BTC">BTC</option>
                                    <option value="ETH">ETH</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Side</label>
                                <select v-model="form.side" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="buy">Buy</option>
                                    <option value="sell">Sell</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Price</label>
                                <input 
                                    v-model.number="form.price" 
                                    type="number" 
                                    step="0.01" 
                                    required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount</label>
                                <input 
                                    v-model.number="form.amount" 
                                    type="number" 
                                    step="0.00000001" 
                                    required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                />
                            </div>
                            <!-- Volume Calculation Preview -->
                            <div v-if="form.price > 0 && form.amount > 0" class="bg-gray-50 p-3 rounded">
                                <div class="text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Total Value:</span>
                                        <span class="font-medium">${{ formatNumber(calculatedVolume) }}</span>
                                    </div>
                                    <div v-if="form.side === 'buy'" class="flex justify-between mt-1">
                                        <span>Commission (1.5%):</span>
                                        <span class="font-medium">${{ formatNumber(calculatedCommission) }}</span>
                                    </div>
                                    <div v-if="form.side === 'buy'" class="flex justify-between mt-1 border-t pt-1">
                                        <span>Total Cost:</span>
                                        <span class="font-bold">${{ formatNumber(calculatedTotal) }}</span>
                                    </div>
                                </div>
                            </div>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Placing Order...' : 'Place Order' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-6 bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Orderbook</h2>
                    <div class="flex gap-2">
                        <!-- Symbol Filter -->
                        <select v-model="filters.symbol" @change="loadOrders" class="rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">All Symbols</option>
                            <option value="BTC">BTC</option>
                            <option value="ETH">ETH</option>
                        </select>
                        <!-- Side Filter -->
                        <select v-model="filters.side" @change="filterOrders" class="rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">All Sides</option>
                            <option value="buy">Buy Only</option>
                            <option value="sell">Sell Only</option>
                        </select>
                    </div>
                </div>
                
                <div v-if="filteredOrders.buy_orders && filteredOrders.buy_orders.length === 0 && filteredOrders.sell_orders && filteredOrders.sell_orders.length === 0" class="text-gray-500 text-center py-4">
                    No open orders
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Buy Orders -->
                    <div>
                        <h3 class="text-sm font-semibold text-green-600 mb-2">Buy Orders</h3>
                        <div class="space-y-1 max-h-64 overflow-y-auto">
                            <div v-for="order in filteredOrders.buy_orders" :key="order.id" class="flex justify-between items-center p-2 bg-green-50 rounded border border-green-200 text-sm">
                                <div>
                                    <span class="font-medium">{{ order.symbol }}</span>
                                    <span class="text-gray-600"> @ ${{ formatNumber(order.price) }}</span>
                                    <span class="text-gray-500"> - {{ formatNumber(order.remaining_amount) }}</span>
                                </div>
                                <button @click="cancelOrder(order.id)" class="text-red-600 hover:text-red-800 text-xs">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <!-- Sell Orders -->
                    <div>
                        <h3 class="text-sm font-semibold text-red-600 mb-2">Sell Orders</h3>
                        <div class="space-y-1 max-h-64 overflow-y-auto">
                            <div v-for="order in filteredOrders.sell_orders" :key="order.id" class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-200 text-sm">
                                <div>
                                    <span class="font-medium">{{ order.symbol }}</span>
                                    <span class="text-gray-600"> @ ${{ formatNumber(order.price) }}</span>
                                    <span class="text-gray-500"> - {{ formatNumber(order.remaining_amount) }}</span>
                                </div>
                                <button @click="cancelOrder(order.id)" class="text-red-600 hover:text-red-800 text-xs">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order History -->
            <div class="mt-6 bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Order History</h2>
                    <div class="flex gap-2">
                        <select v-model="historyFilters.status" @change="loadHistory" class="rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">All Status</option>
                            <option value="1">Open</option>
                            <option value="2">Filled</option>
                            <option value="3">Cancelled</option>
                        </select>
                        <select v-model="historyFilters.symbol" @change="loadHistory" class="rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">All Symbols</option>
                            <option value="BTC">BTC</option>
                            <option value="ETH">ETH</option>
                        </select>
                    </div>
                </div>
                <div v-if="orderHistory.length === 0" class="text-gray-500 text-center py-4">
                    No order history
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Symbol</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Side</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filled</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="order in orderHistory" :key="order.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ order.symbol }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span :class="order.side === 'buy' ? 'text-green-600' : 'text-red-600'" class="font-medium uppercase">
                                        {{ order.side }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">${{ formatNumber(order.price) }}</td>
                                <td class="px-4 py-3 text-sm">{{ formatNumber(order.amount) }}</td>
                                <td class="px-4 py-3 text-sm">{{ formatNumber(order.filled_amount || 0) }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span :class="getStatusClass(order.status)" class="px-2 py-1 rounded text-xs font-medium">
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(order.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Layout from './Layout.vue';

const props = defineProps({
    profile: Object,
    orders: Object,
    orderErrors: {
        type: Object,
        default: () => ({}),
    },
});

const form = ref({
    symbol: 'BTC',
    side: 'buy',
    price: 0,
    amount: 0,
    processing: false,
});

const filters = ref({
    symbol: 'BTC',
    side: '',
});

const historyFilters = ref({
    status: '',
    symbol: '',
});

const orderHistory = ref([]);
const toast = ref({ show: false, message: '', type: 'success' });

const filteredOrders = computed(() => {
    if (!props.orders) return { buy_orders: [], sell_orders: [] };
    
    let buyOrders = props.orders.buy_orders || [];
    let sellOrders = props.orders.sell_orders || [];
    
    if (buyOrders && typeof buyOrders === 'object' && 'data' in buyOrders) {
        buyOrders = buyOrders.data || [];
    }
    if (sellOrders && typeof sellOrders === 'object' && 'data' in sellOrders) {
        sellOrders = sellOrders.data || [];
    }
    
    if (!Array.isArray(buyOrders)) buyOrders = [];
    if (!Array.isArray(sellOrders)) sellOrders = [];
    
    if (filters.value.symbol) {
        buyOrders = buyOrders.filter(o => o.symbol === filters.value.symbol);
        sellOrders = sellOrders.filter(o => o.symbol === filters.value.symbol);
    }
    
    if (filters.value.side === 'buy') {
        sellOrders = [];
    } else if (filters.value.side === 'sell') {
        buyOrders = [];
    }
    
    return { buy_orders: buyOrders, sell_orders: sellOrders };
});

const calculatedVolume = computed(() => {
    if (form.value.price > 0 && form.value.amount > 0) {
        return form.value.price * form.value.amount;
    }
    return 0;
});

const calculatedCommission = computed(() => {
    return calculatedVolume.value * 0.015;
});

const calculatedTotal = computed(() => {
    if (form.value.side === 'buy') {
        return calculatedVolume.value + calculatedCommission.value;
    }
    return calculatedVolume.value;
});

const toastClass = computed(() => {
    return toast.value.type === 'success' 
        ? 'bg-green-500 text-white' 
        : 'bg-red-500 text-white';
});

const showToast = (message, type = 'success') => {
    toast.value = { show: true, message, type };
    setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

const formatNumber = (num) => {
    if (!num && num !== 0) return '0';
    return parseFloat(num).toLocaleString('en-US', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 8 
    });
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString();
};

const getStatusLabel = (status) => {
    const labels = { 1: 'Open', 2: 'Filled', 3: 'Cancelled' };
    return labels[status] || 'Unknown';
};

const getStatusClass = (status) => {
    const classes = {
        1: 'bg-yellow-100 text-yellow-800',
        2: 'bg-green-100 text-green-800',
        3: 'bg-gray-100 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const loadOrders = () => {
    router.reload({ 
        only: ['orders'],
        data: { symbol: filters.value.symbol || 'BTC' }
    });
};

const filterOrders = () => {
};

const loadHistory = () => {
    if (!page.props.auth?.user) {
        return;
    }
    
    window.axios.get('/api/orders/history', {
        params: {
            status: historyFilters.value.status,
            symbol: historyFilters.value.symbol,
        }
    })
        .then(response => {
            orderHistory.value = response.data.data || [];
        })
        .catch(error => {
            if (error.response?.status !== 401) {
                console.error('Failed to load order history:', error);
                showToast('Failed to load order history', 'error');
            }
        });
};

const submit = () => {
    form.value.processing = true;
    window.axios.post('/api/orders', {
        symbol: form.value.symbol,
        side: form.value.side,
        price: form.value.price,
        amount: form.value.amount,
    })
        .then(() => {
            showToast('Order placed successfully!', 'success');
            form.value.symbol = 'BTC';
            form.value.side = 'buy';
            form.value.price = 0;
            form.value.amount = 0;
            router.reload({ only: ['profile', 'orders'] });
            loadHistory();
        })
        .catch((error) => {
            showToast(error.response?.data?.message || 'Failed to create order', 'error');
        })
        .finally(() => {
            form.value.processing = false;
        });
};

const cancelOrder = (id) => {
    if (confirm('Are you sure you want to cancel this order?')) {
        window.axios.post(`/api/orders/${id}/cancel`)
            .then(() => {
                showToast('Order cancelled successfully!', 'success');
                router.reload({ only: ['profile', 'orders'] });
                loadHistory();
            })
            .catch((error) => {
                showToast(error.response?.data?.message || 'Failed to cancel order', 'error');
            });
    }
};

// Set up Pusher listener
let echoListener = null;
const page = usePage();

onMounted(() => {
    if (!page.props.auth?.user) {
        return;
    }
    
    const userId = page.props.auth.user.id;
    
    if (window.Echo && userId) {
        try {
            echoListener = window.Echo.private(`user.${userId}`)
                .listen('.order.matched', (e) => {
                    showToast('Order matched! Trade executed.', 'success');
                    router.reload({ only: ['profile', 'orders'] });
                    loadHistory();
                });
        } catch (error) {
            console.warn('Failed to set up Pusher listener:', error);
        }
    }
    
    loadHistory();
});

onUnmounted(() => {
    if (echoListener && window.Echo) {
        try {
            const userId = page.props.auth?.user?.id;
            if (userId) {
                window.Echo.leave(`user.${userId}`);
            }
        } catch (error) {
            console.warn('Failed to clean up Pusher listener:', error);
        }
        echoListener = null;
    }
});
</script>
