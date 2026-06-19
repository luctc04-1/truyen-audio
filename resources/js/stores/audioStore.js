import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAudioStore = defineStore('audio', () => {
    const currentTrack = ref(null);
    const isPlaying = ref(false);
    const currentTime = ref(0);
    const duration = ref(0);
    const playlist = ref([]);
    const currentIndex = ref(0);

    const currentTrackData = computed(() => playlist.value[currentIndex.value]);

    /**
     * Play audio
     *
     * @param {Object} track
     */
    const play = (track = null) => {
        if (track) {
            currentTrack.value = track;
        }
        isPlaying.value = true;
    };

    /**
     * Pause audio
     */
    const pause = () => {
        isPlaying.value = false;
    };

    /**
     * Toggle play/pause
     */
    const togglePlay = () => {
        isPlaying.value = !isPlaying.value;
    };

    /**
     * Set playlist
     *
     * @param {Array} tracks
     */
    const setPlaylist = (tracks) => {
        playlist.value = tracks;
        currentIndex.value = 0;
    };

    /**
     * Play next track
     */
    const nextTrack = () => {
        if (currentIndex.value < playlist.value.length - 1) {
            currentIndex.value++;
            currentTrack.value = playlist.value[currentIndex.value];
            play();
        }
    };

    /**
     * Play previous track
     */
    const previousTrack = () => {
        if (currentIndex.value > 0) {
            currentIndex.value--;
            currentTrack.value = playlist.value[currentIndex.value];
            play();
        }
    };

    /**
     * Update current time
     *
     * @param {number} time
     */
    const updateCurrentTime = (time) => {
        currentTime.value = time;
    };

    /**
     * Update duration
     *
     * @param {number} dur
     */
    const updateDuration = (dur) => {
        duration.value = dur;
    };

    return {
        currentTrack,
        isPlaying,
        currentTime,
        duration,
        playlist,
        currentIndex,
        currentTrackData,
        play,
        pause,
        togglePlay,
        setPlaylist,
        nextTrack,
        previousTrack,
        updateCurrentTime,
        updateDuration,
    };
});
