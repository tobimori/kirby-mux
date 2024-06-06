<template>
	<div class="k-file-preview" :data-has-focus="Boolean(focus)">
		<!-- Thumb -->
		<div class="k-file-preview-thumb-column">
			<div class="k-file-preview-thumb">
				<!-- Image with focus picker -->
				<template v-if="image.src">
					<k-coords-input
						:disabled="!focusable"
						:value="focus"
						@input="setFocus($event)"
					>
						<img v-bind="image" @dragstart.prevent />
					</k-coords-input>

					<k-button
						icon="dots"
						size="xs"
						style="color: var(--color-gray-500)"
						@click="$refs.dropdown.toggle()"
					/>
					<k-dropdown-content ref="dropdown" :options="options" theme="light" />
				</template>

				<template v-else-if="url.startsWith('https://stream.mux.com/')">
					<mux-video
						:playback-id="url.match(/\/([^\/]+)\.[^.]+$/)[1]"
						controls
					></mux-video>
				</template>

				<!-- Icon -->
				<k-icon
					v-else
					:color="$helper.color(image.color)"
					:type="image.icon"
					class="k-item-icon"
				/>
			</div>
		</div>

		<!-- Details -->
		<div class="k-file-preview-details">
			<dl>
				<div v-for="detail in details" :key="detail.title">
					<dt>{{ detail.title }}</dt>
					<dd>
						<k-link
							v-if="detail.link"
							:to="detail.link"
							tabindex="-1"
							target="_blank"
						>
							/{{ detail.text }}
						</k-link>
						<template v-else>
							{{ detail.text }}
						</template>
					</dd>
				</div>

				<div v-if="image.src" class="k-file-preview-focus-info">
					<dt>{{ $t("file.focus.title") }}</dt>
					<dd>
						<k-file-focus-button
							v-if="focusable"
							ref="focus"
							:focus="focus"
							@set="setFocus"
						/>
						<template v-else-if="focus">
							{{ focus.x }}% {{ focus.y }}%
						</template>
						<template v-else>–</template>
					</dd>
				</div>
			</dl>
		</div>
	</div>
</template>

<script>
import "@mux/mux-video"

export default {
	extends: "k-file-preview"
}
</script>

<style scoped lang="scss">
mux-video {
	margin: calc(var(--spacing-12) * -1);
	height: calc(var(--spacing-12) * 2 + 100%);
}
</style>
