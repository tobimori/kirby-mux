<template>
	<div class="k-mux-video-preview">
		<k-file-preview-frame :options="options">
			<mux-video
				v-if="playbackId && status === 'ready'"
				:playback-id="playbackId"
				controls
				playsinline
			></mux-video>
			<div v-else-if="status === 'preparing'" class="k-mux-video-status">
				<k-icon type="loader" />
				<p>{{ $t("mux.status.processing") }}</p>
			</div>
			<div v-else-if="status === 'errored'" class="k-mux-video-status">
				<k-icon type="alert" />
				<p>{{ $t("mux.status.error") }}</p>
			</div>
			<div v-else class="k-mux-video-status">
				<k-icon type="video" />
				<p>Status: {{ status || "Unknown" }}</p>
				<p v-if="playbackId">Playback ID: {{ playbackId }}</p>
			</div>
		</k-file-preview-frame>

		<k-file-preview-details :details="details" />
	</div>
</template>

<script>
import "@mux/mux-video"

export default {
	props: {
		details: Array,
		url: String,
		playbackId: String,
		status: String,
		assetId: String,
		duration: Number,
		niceDuration: String
	},
	computed: {
		options() {
			if (this.status === "ready" && this.url) {
				return [
					{
						icon: "download",
						text: this.$t("download"),
						link: this.url,
						download: true
					}
				]
			}
			return []
		}
	}
}
</script>

<style>
.k-mux-video-preview {
	.k-file-preview-frame-column {
		aspect-ratio: 16/9;
	}

	.k-file-preview-frame {
		padding: 0;
	}
	mux-video {
		width: 100%;
		height: 100%;
		display: block;
		object-fit: contain;
	}
}

.k-mux-video-status {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100%;
	gap: var(--spacing-2);
	color: var(--color-gray-600);

	.k-icon {
		width: 3rem;
		height: 43rem8px;
	}
	p {
		font-size: var(--text-sm);
	}
}

@container (min-width: 60rem) {
	.k-mux-video-preview {
		grid-template-columns: 50% auto;
	}
}
</style>
