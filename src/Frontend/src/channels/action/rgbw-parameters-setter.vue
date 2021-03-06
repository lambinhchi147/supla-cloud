<template>
    <div>
        <div :class="brightnessClass">
            <label>{{ $t('Brightness') }}</label>
            <span class="input-group">
                <input type="number"
                    min="0"
                    max="100"
                    step="5"
                    class="form-control"
                    maxlength="3"
                    v-model="brightness"
                    @change="onChange()">
                <span class="input-group-addon">%</span>
            </span>
        </div>
        <div :class="hueClass">
            <label>{{ $t('Color') }}</label>
            <div class="radio">
                <label>
                    <input type="radio"
                        value="choose"
                        v-model="hueMode"
                        @change="onChange()">
                    {{ $t('Choose') }}
                </label>
            </div>
            <div v-if="hueMode == 'choose'">
                <hue-colorpicker v-model="hue"
                    @input="onChange()"></hue-colorpicker>
            </div>
            <div class="radio">
                <label>
                    <input type="radio"
                        value="random"
                        v-model="hueMode"
                        @change="onChange()">
                    {{ $t('Random') }}
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio"
                        value="white"
                        v-model="hueMode"
                        @change="onChange()">
                    {{ $t('White') }}
                </label>
            </div>
        </div>
        <div :class="colorBrightnessClass">
            <label>{{ $t('Color brightness') }}</label>
            <span class="input-group">
                <input type="number"
                    min="0"
                    max="100"
                    step="5"
                    class="form-control"
                    maxlength="3"
                    v-model="colorBrightness"
                    @change="onChange()">
                <span class="input-group-addon">%</span>
            </span>
        </div>
    </div>
</template>

<script type="text/babel">
    import HueColorpicker from "./hue-colorpicker.vue";

    export default {
        name: 'rgbw-parameters-setter',
        components: {HueColorpicker},
        props: ['channelFunction', 'value'],
        data() {
            return {
                hue: 0,
                hueMode: 'choose',
                colorBrightness: 0,
                brightness: 0
            };
        },
        mounted() {
            if (this.value) {
                if (this.value.hue == 'random') {
                    this.hueMode = 'random';
                } else if (this.value.hue == 'white') {
                    this.hueMode = 'white';
                } else {
                    this.hue = this.value.hue || 0;
                }
                this.colorBrightness = this.value.color_brightness || 0;
                this.brightness = this.value.brightness || 0;

                this.onChange();
            }
        },
        methods: {
            onChange() {
                let value = {};
                if (this.brightnessClass !== 'hidden') {
                    this.brightness = this.ensureBetween(this.brightness, 0, 100);
                    value.brightness = this.brightness;
                }
                if (this.hueClass !== 'hidden') {
                    if (this.hueMode === 'choose') {
                        value.hue = this.ensureBetween(this.hue, 0, 360);
                    } else {
                        value.hue = this.hueMode === 'random' ? 'random' : 'white';
                    }
                }
                if (this.colorBrightnessClass !== 'hidden') {
                    this.colorBrightness = this.ensureBetween(this.colorBrightness, 0, 100);
                    value.color_brightness = this.colorBrightness;
                }
                this.$emit('input', value);
            },
            ensureBetween(value, min, max) {
                if (value < min) {
                    return min;
                } else if (value > max) {
                    return max;
                } else {
                    return +value;
                }
            }
        },
        computed: {
            brightnessClass() {
                return {'DIMMER': 'col-xs-12', 'RGBLIGHTING': 'hidden'}[this.channelFunction.name] || 'col-xs-4';
            },
            hueClass() {
                return {'DIMMER': 'hidden', 'RGBLIGHTING': 'col-xs-6'}[this.channelFunction.name] || 'col-xs-4';
            },
            colorBrightnessClass() {
                return {'DIMMER': 'hidden', 'RGBLIGHTING': 'col-xs-6'}[this.channelFunction.name] || 'col-xs-4';
            },
        },
        watch: {
            channelFunction() {
                this.onChange();
            }
        }
    };
</script>
