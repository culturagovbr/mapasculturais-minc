
app.component('mc-accordion', {
    template: $TEMPLATES['mc-accordion'],

    props: {
        withText: {
            type: Boolean,
            default: false,
        },
        openOnArrow: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            active: false,
        }
    },
    methods: {
        toggle(event) {
            if (this.openOnArrow && !event.target.closest('.mc-accordion__close')) {
                return; 
            }
            
            this.active = !this.active;
        },
    },
});
