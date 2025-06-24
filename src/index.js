import MuxVideoPreview from "./components/MuxVideoPreview.vue";
import Item from "./core/Item.vue";

panel.plugin("tobimori/mux", {
	components: {
		"k-item": Item,
		"k-mux-video-preview": MuxVideoPreview,
	},
});
