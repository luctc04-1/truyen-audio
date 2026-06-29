import ApiService from './ApiService';
import { extractApiPayload } from '@/utils/helpers';

class FollowService {
    getFollowedIds() {
        return ApiService.get('/auth/me/follows')
            .then(extractApiPayload)
            .then((data) => (data?.ids ?? []).map(String));
    }

    toggle(seriesId) {
        return ApiService.post(`/series/${seriesId}/follow`).then(extractApiPayload);
    }
}

export default new FollowService();
