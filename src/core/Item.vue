<template>
	<div
		v-bind="data"
		:class="layout ? 'k-' + layout + '-item' : false"
		:data-has-image="hasFigure"
		:data-layout="layout"
		:data-theme="theme"
		class="k-item"
		@click="$emit('click', $event)"
		@dragstart="$emit('drag', $event)"
	>
		<!-- Image -->
		<slot name="image">
			<!-- Video Player -->
			<k-frame
				v-if="showMuxPlayer"
				v-bind="image"
				element="figure"
				class="k-image-frame k-image"
			>
				<mux-video
					:playback-id="playbackId"
					:layout="layout"
					controls
				></mux-video>
			</k-frame>

			<k-item-image
				v-else-if="hasFigure"
				:image="image"
				:layout="layout"
				:width="width"
			/>
		</slot>

		<!-- Sort handle -->
		<k-sort-handle v-if="sortable" class="k-item-sort-handle" tabindex="-1" />

		<!-- Content -->
		<div class="k-item-content">
			<h3 class="k-item-title" :title="title">
				<k-link v-if="link !== false" :target="target" :to="link">
					<!-- eslint-disable-next-line vue/no-v-html -->
					<span v-html="text ?? '–'" />
				</k-link>
				<!-- eslint-disable-next-line vue/no-v-html -->
				<span v-else v-html="text ?? '–'" />
			</h3>
			<!-- eslint-disable-next-line vue/no-v-html -->
			<p v-if="info" class="k-item-info" v-html="info" />
		</div>

		<div
			class="k-item-options"
			:data-only-option="!buttons?.length || (!options && !$slots.options)"
		>
			<!-- Buttons -->
			<k-button
				v-for="(button, buttonIndex) in buttons"
				:key="'button-' + buttonIndex"
				v-bind="button"
			/>

			<!-- Options -->
			<slot name="options">
				<k-options-dropdown
					v-if="options"
					:options="options"
					class="k-item-options-dropdown"
					@option="onOption"
				/>
			</slot>
		</div>
	</div>
</template>

<script>
export default {
	extends: "k-item",
	computed: {
		showMuxPlayer() {
			return this.layout === "cards" && this.image?.player === true
		},
		playbackId() {
			// this is pretty hacky but works since we always use the playback id to store thumbnail
			return this.image.url.split("/").pop().replace(".png", "")
		}
	}
}
</script>

<style scoped lang="scss">
mux-video {
	position: absolute;
	inset: 0;
	height: 100%;
	width: 100%;
	object-fit: var(--fit);
	z-index: 10;
}
</style>
