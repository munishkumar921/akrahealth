<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center  sm:pt-0 bg-gray-100">
        <div>
            <slot name="logo" />
        </div>

        <div class="  py-4 shadow-md overflow-hidden sm:rounded-lg">
            <slot />
        </div>
    </div>
</template>
