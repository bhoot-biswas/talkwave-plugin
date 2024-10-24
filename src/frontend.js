import { store, getElement, getContext } from "@wordpress/interactivity";
import { Howl, Howler } from "howler";

import "./frontend.scss";

function padNum(num) {
	return ("" + (num + 100)).substring(1);
}

function formatTime(totalSeconds) {
	const hours = Math.floor(totalSeconds / 3600);
	totalSeconds %= 3600;
	const minutes = Math.floor(totalSeconds / 60);
	const seconds = Math.floor(totalSeconds % 60);
	return hours > 0
		? `${padNum(hours)}:${padNum(minutes)}:${padNum(seconds)}`
		: `${padNum(minutes)}:${padNum(seconds)}`;
}

const { state, actions } = store("talkwave", {
	state: {
		playlists: {},
		currentPlaylistId: null, // updated later in play()
		index: 0,
		duration: 0,
		seek: 0,
		loading: false,
		playing: false,
		rate: 1,
		muted: false,
		get hidePlayButton() {
			return state.loading || state.playing;
		},
		get hidePauseButton() {
			return state.loading || !state.playing;
		},
		get hideRipple() {
			return !state.loading;
		},
		get durationHTML() {
			return formatTime(Math.round(state.duration));
		},
		get timerHTML() {
			return formatTime(Math.round(state.seek));
		},
		get progress() {
			return ((state.seek / state.duration) * 100 || 0) + "%";
		},
		get getRate() {
			return state.rate + "x";
		},
		get podcastTitle() {
			const currentPlaylistId =
				state.currentPlaylistId || Object.keys(state.playlists)[0];
			return state.playlists[currentPlaylistId][state.index].podcast_title;
		},
		get episodeTitle() {
			const currentPlaylistId =
				state.currentPlaylistId || Object.keys(state.playlists)[0];
			return state.playlists[currentPlaylistId][state.index].title;
		},
		get episodeImage() {
			const currentPlaylistId =
				state.currentPlaylistId || Object.keys(state.playlists)[0];
			return state.playlists[currentPlaylistId][state.index].album_art["src"];
		},
		playlistLoading: () => {
			const { playlist_id } = getContext();
			return state.currentPlaylistId === playlist_id && state.loading;
		},
		playlistPlaying: () => {
			const { playlist_id } = getContext();
			return state.currentPlaylistId === playlist_id && state.playing;
		}
	},
	callbacks: {
		// setPlaylist updates the state with a new playlist or replaces an existing one
		setPlaylist: () => {
			const { playlist_id, playlist } = getContext();
			// Validate if playlistId and playlistItems are provided
			if (!playlist_id || !Array.isArray(playlist)) {
				console.error("Invalid playlistId or playlistItems.");
				return;
			}

			// Add or update the playlist in the state
			state.playlists[playlist_id] = playlist;
		},
	},
	actions: {
		play: (index = 0, playlistId = null) => {
			const context = getContext();

			// Check if context has a playlistId and use it if provided
			if (context && context.playlist_id) {
				playlistId = context.playlist_id;
			}

			if (!playlistId && state.currentPlaylistId) {
				playlistId = state.currentPlaylistId;
			}

			// If playlistId is not provided, default to the first playlist ID
			if (!playlistId) {
				const firstPlaylistId = Object.keys(state.playlists)[0];
				playlistId = firstPlaylistId;
			}

			// Check if the playlist exists
			if (!state.playlists[playlistId]) {
				console.error(`Playlist with ID ${playlistId} not found.`);
				return;
			}

			// Ensure index is within bounds
			index = index < state.playlists[playlistId].length ? index : 0;

			// Load playlist and track data
			let playlist = state.playlists[playlistId];
			let track = playlist[index];
			let sound;

			if (track.howl) {
				// Use existing Howl instance if already loaded
				sound = track.howl;
			} else {
				// Create new Howl instance for the track
				sound = track.howl = new Howl({
					src: [track.audio_file],
					html5: true,
					onplay: function () {
						state.loading = false;
						state.playing = true;
						state.duration = sound.duration();
						requestAnimationFrame(actions.step);
					},
					onload: function () {
						state.loaded = true;
					},
					onend: function () {
						actions.skip("next", playlistId);
					},
					onpause: function () {
						state.playing = false;
					},
					onstop: function () {
						state.playing = false;
					},
					onseek: function () {
						requestAnimationFrame(actions.step);
					},
				});
			}

			sound.play();
			state.loading = sound.state() !== "loaded";
			state.playing = sound.state() === "loaded";
			state.currentPlaylistId = playlistId; // Track current playlist ID
			state.index = index;
		},
		handlePlaylist: () => {
			const { playlist_id } = getContext();

			if (state.currentPlaylistId === playlist_id) {
				if (state.playing) {
					actions.pause();
				} else {
					actions.play(state.index, playlist_id);
				}
			} else {
				if (state.playing) {
					actions.pause();
				}

				actions.play(0, playlist_id);
			}
		},
		pause: () => {
			let sound = state.playlists[state.currentPlaylistId][state.index].howl;
			if (sound) sound.pause();
		},
		skip: (direction, playlistId = state.currentPlaylistId) => {
			let index = state.index;
			let playlist = state.playlists[playlistId];

			if (direction === "prev") {
				index = index - 1 < 0 ? playlist.length - 1 : index - 1;
			} else {
				index = (index + 1) % playlist.length;
			}

			actions.skipTo(index, playlistId);
		},
		skipTo: (index, playlistId) => {
			let sound = state.playlists[playlistId][state.index].howl;
			if (sound) sound.stop();
			actions.play(index, playlistId);
		},
		volume: (val) => {
			Howler.volume(val);
		},
		seek: (per) => {
			let sound = state.playlists[state.currentPlaylistId][state.index].howl;
			if (sound && sound.playing()) {
				sound.seek(sound.duration() * per);
			}
		},
		rewind: () => {
			const { ref } = getElement();
			let sound = state.playlists[state.currentPlaylistId][state.index].howl;
			let seek = (sound.seek() || 0) - parseFloat(ref.dataset.skip);
			if (sound.playing()) sound.seek(seek);
		},
		fastForward: () => {
			const { ref } = getElement();
			let sound = state.playlists[state.currentPlaylistId][state.index].howl;
			let seek = (sound.seek() || 0) + parseFloat(ref.dataset.skip);
			if (sound.playing()) sound.seek(seek);
		},
		step: () => {
			let sound = state.playlists[state.currentPlaylistId][state.index].howl;
			state.seek = sound.seek() || 0;
			if (sound.playing()) {
				requestAnimationFrame(actions.step);
			}
		},
		handleRate: () => {
			let sound = state.playlists[state.currentPlaylistId][state.index].howl;
			let newRate =
				state.rate < 2 ? (parseFloat(state.rate) + 0.2).toFixed(1) : 0.4;
			state.rate = newRate;
			sound.rate(newRate);
		},
		toggleMute: () => {
			let sound = state.playlists[state.currentPlaylistId][state.index].howl;
			state.muted = !state.muted;
			sound.mute(state.muted);
		},
		scrub: (e) => {
			const { ref } = getElement();
			const { left, width } = ref.getBoundingClientRect();
			const seek = (e.clientX - left) / width;
			actions.seek(seek);
		},
	},
});
