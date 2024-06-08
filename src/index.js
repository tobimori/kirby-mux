import FilePreview from "./core/FilePreview.vue"
import Item from "./core/Item.vue"

panel.plugin("tobimori/mux", {
	components: {
		"k-file-preview": FilePreview,
		"k-item": Item
	}
})
