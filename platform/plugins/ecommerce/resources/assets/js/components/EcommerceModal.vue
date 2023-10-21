<script>
import { defineComponent } from 'vue'

export default defineComponent({
    props: {
        id: {
            type: String,
            required: true,
        },
        title: String,
        okTitle: String,
        cancelTitle: String,
    },

    data() {
        return {
            modal: null,
        }
    },

    mounted() {
        this.$emit('shown')

        this.modal = new bootstrap.Modal(document.getElementById(this.id))

        $event.on(`ec-modal:open`, (id) => {
            if (id === this.id) {
                this.modal.show()
            }
        })

        $event.on('ec-modal:close', (id) => {
            if (id === this.id) {
                this.modal.hide()
            }
        })
    },
})
</script>

<template>
    <div class="modal fade" :id="id" tabindex="-1" :aria-labelledby="`${id}Label`" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <header class="modal-header">
                    <h5 class="modal-title" :id="`${id}Label`" v-text="title" />
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </header>
                <div class="modal-body">
                    <slot />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" v-text="cancelTitle" />
                    <button type="button" class="btn btn-primary" @click="$emit('ok', $event)" v-text="okTitle" />
                </div>
            </div>
        </div>
    </div>
</template>
