<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage();
const siteSettings = page.props.site_settings as any || {};
</script>

<template>
    <div class="relative min-h-screen flex">
        <!-- Left Side: Image / Gradient -->
        <div class="hidden lg:flex lg:w-[45%] relative bg-indigo-950 overflow-hidden">
            <img src="/auth_bg.png" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-60" alt="Background" />
            
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/80 via-purple-900/70 to-blue-950/90 z-10"></div>
            
            <div class="relative z-20 flex flex-col justify-center px-12 xl:px-20 h-full text-white">
                <Link :href="route('home')" class="mb-10 p-4 bg-white/10 backdrop-blur-md rounded-2xl inline-flex self-start border border-white/20 shadow-2xl hover:bg-white/20 transition-all duration-300 transform hover:scale-105 animate-drop-down">
                    <div v-if="!siteSettings.logo_path" class="flex items-center justify-center shrink-0 w-12 h-12 rounded-full bg-[radial-gradient(circle_at_35%_30%,_#C89B3C_0%,_#A87F2B_70%)] text-[#14213D] font-[Fraunces,serif] font-bold text-xl shadow-[0_0_0_3px_rgba(200,155,60,0.25)]">SV</div>
                    <img v-else :src="'/storage/' + siteSettings.logo_path" class="h-12 object-contain" alt="Logo" />
                </Link>
                
                <h2 class="text-4xl xl:text-5xl font-extrabold tracking-tight mb-6 leading-[1.15]">
                    Welcome to <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 via-indigo-300 to-purple-300">
                        {{ siteSettings.institute_name || 'Ideal Professional Institute' }}
                    </span>
                </h2>
                <p class="text-lg text-indigo-100 max-w-lg leading-relaxed mb-10 font-medium opacity-90">
                    {{ siteSettings.tagline || 'Experience a world-class education platform. Manage your academics, check results, and stay updated effortlessly.' }}
                </p>

                <div class="flex gap-4 mt-auto mb-10">
                    <div class="flex items-center gap-3 bg-black/30 backdrop-blur-md px-5 py-2.5 rounded-full border border-white/10 shadow-lg">
                        <div class="w-2.5 h-2.5 rounded-full bg-green-400 animate-pulse shadow-[0_0_10px_rgba(74,222,128,0.8)]"></div>
                        <span class="text-sm font-semibold tracking-wide text-indigo-50">System Online</span>
                    </div>
                </div>
            </div>
            
            <!-- Decorative shapes -->
            <div class="absolute -top-32 -right-32 w-96 h-96 bg-blue-500 rounded-full mix-blend-screen filter blur-[100px] opacity-40 animate-blob"></div>
            <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[100px] opacity-40 animate-blob animation-delay-2000"></div>
        </div>

        <!-- Right Side: Form -->
        <div class="w-full lg:w-[55%] flex flex-col justify-center items-center p-6 sm:p-12 bg-background relative z-10 min-h-screen">
            <!-- Mobile Header -->
            <div class="lg:hidden flex flex-col items-center gap-4 mb-8 w-full max-w-md">
                <Link :href="route('home')" class="flex flex-col items-center gap-3 font-medium animate-drop-down">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 shadow-sm border border-indigo-100 dark:border-indigo-900 overflow-hidden">
                         <div v-if="!siteSettings.logo_path" class="flex items-center justify-center shrink-0 w-11 h-11 rounded-full bg-[radial-gradient(circle_at_35%_30%,_#C89B3C_0%,_#A87F2B_70%)] text-[#14213D] font-[Fraunces,serif] font-bold text-lg shadow-[0_0_0_3px_rgba(200,155,60,0.25)]">SV</div>
                         <img v-else :src="'/storage/' + siteSettings.logo_path" class="size-10 object-contain" alt="Logo" />
                    </div>
                    <span class="text-2xl font-bold text-center bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400">
                        {{ siteSettings.institute_name || 'Ideal Professional Institute' }}
                    </span>
                </Link>
            </div>

            <!-- Form Container -->
            <div class="w-full max-w-[420px] mx-auto animate-in fade-in slide-in-from-bottom-8 duration-700">
                <div class="flex flex-col gap-2 mb-8 text-center lg:text-left">
                    <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ title }}</h1>
                    <p class="text-muted-foreground font-medium">{{ description }}</p>
                </div>

                <div class="bg-card text-card-foreground shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:border border-border/40 rounded-2xl p-6 sm:p-8 relative overflow-hidden backdrop-blur-sm">
                    <slot />
                </div>
                
                <div class="mt-8 text-center">
                    <Link :href="route('home')" class="text-sm font-medium text-muted-foreground hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors inline-flex items-center gap-2 group">
                        <span class="group-hover:-translate-x-1 transition-transform">&larr;</span> Back to Home
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media (min-width: 1280px) {
    .xl\:px-20 {
        padding-top: 50px;
    }
}

@keyframes dropDown {
    0% { transform: translateY(-50px); opacity: 0; }
    100% { transform: translateY(0); opacity: 1; }
}
.animate-drop-down {
    animation: dropDown 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
</style>
