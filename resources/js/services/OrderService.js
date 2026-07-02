import ApiService from '@/services/ApiService';
import { extractApiPayload } from '@/utils/helpers';

class OrderService {
    async checkout(planId) {
        return extractApiPayload(await ApiService.post('/orders', { plan_id: planId }));
    }

    async status(orderCode) {
        return extractApiPayload(await ApiService.get(`/orders/${orderCode}`));
    }
}

export default new OrderService();
