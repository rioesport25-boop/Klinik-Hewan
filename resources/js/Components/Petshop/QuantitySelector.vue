<script setup>
const props = defineProps({
    modelValue: {
        type: Number,
        default: 1,
    },
    min: {
        type: Number,
        default: 1,
    },
    max: {
        type: Number,
        default: Infinity,
    },
    size: {
        type: String,
        default: 'md', // md | lg
    },
});

const emit = defineEmits(['update:modelValue']);

const decrease = () => {
    if (props.modelValue > props.min) {
        emit('update:modelValue', props.modelValue - 1);
    }
};

const increase = () => {
    if (props.modelValue < props.max) {
        emit('update:modelValue', props.modelValue + 1);
    }
};

const onInput = (event) => {
    const value = Number(event.target.value) || props.min;
    const clamped = Math.max(props.min, Math.min(value, props.max));
    emit('update:modelValue', clamped);
};

const sizeClasses = {
    md: {
        button: 'px-3 py-2 text-sm',
        input: 'w-14 text-sm',
    },
    lg: {
        button: 'px-4 py-3 text-base',
        input: 'w-16 text-base',
    },
};
</script>

<template>
    <div class="inline-flex items-center rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <button
            type="button"
            @click="decrease"
            :disabled="modelValue <= min"
            :class="[
                'rounded-l-lg font-semibold text-gray-600 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:text-gray-300 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus-visible:ring-offset-gray-900 disabled:dark:text-gray-500',
                sizeClasses[size].button,
            ]"
        >
            -
        </button>
        <input
            :value="modelValue"
            @input="onInput"
            type="number"
            :min="min"
            :max="max"
            :class="[
                'border-x border-gray-200 text-center font-semibold text-gray-700 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100',
                sizeClasses[size].input,
            ]"
        >
        <button
            type="button"
            @click="increase"
            :disabled="modelValue >= max"
            :class="[
                'rounded-r-lg font-semibold text-gray-600 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:text-gray-300 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus-visible:ring-offset-gray-900 disabled:dark:text-gray-500',
                sizeClasses[size].button,
            ]"
        >
            +
        </button>
    </div>
</template>
