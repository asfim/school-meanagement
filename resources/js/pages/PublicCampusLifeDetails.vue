<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface CampusLifeItem {
    id: number;
    title: string;
    description: string | null;
    image_path: string | null;
    sort_order: number;
    is_active: boolean;
}

const props = defineProps<{
    item: CampusLifeItem;
}>();

const mobileMenuOpen = ref(false);

const page = usePage();
const siteSettings = computed(() => page.props.site_settings as {
    institute_name: string;
    tagline: string;
    logo_path: string | null;
    favicon_path: string | null;
});

const excerpt = computed(() => {
    if (!props.item.description) return '';
    return props.item.description.substring(0, 160).replace(/\s+/g, ' ').trim() + '...';
});
</script>

<template>
    <!-- SEO Meta Title & Meta Description -->
    <Head>
        <title>{{ item.title }} — Campus Life — {{ siteSettings.institute_name }}</title>
        <meta name="description" :content="excerpt" />
    </Head>

    <div class="nd-root">
        <!-- Paper texture -->
        <div class="nd-texture" aria-hidden="true"></div>

        <!-- Header -->
        <header class="nd-header">
            <div class="nd-topbar">
                <Link href="/" class="nd-brand">
                    <img v-if="siteSettings.logo_path" :src="'/storage/' + siteSettings.logo_path" class="w-10 h-10 object-contain rounded-md mr-2" alt="Logo" />
                    <div v-else class="nd-crest">SV</div>
                    <div class="nd-brand-text">
                        <div class="nd-school-name">{{ siteSettings.institute_name }}</div>
                        <div class="nd-school-tag">{{ siteSettings.tagline }}</div>
                    </div>
                </Link>
                <nav class="nd-nav">
                    <Link href="/" class="nd-nav-link">Home</Link>
                    <Link href="/#notices" class="nd-nav-link">Notice Board</Link>
                    <Link href="/#about" class="nd-nav-link">About Us</Link>
                    <Link href="/#programs" class="nd-nav-link">Programs</Link>
                    <Link href="/#campus-life" class="nd-nav-link nd-nav-link--active">Campus Life</Link>
                    <Link href="/result" class="nd-nav-link">Results</Link>
                    <Link href="/#contact" class="nd-nav-link">Contact</Link>
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="nd-nav-cta"
                    >Dashboard</Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="nd-nav-cta"
                    >Staff Login</Link>
                </nav>

                <!-- Burger Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="sv-burger-btn" aria-label="Toggle Menu">
                    <span :class="{ 'sv-burger-line--open': mobileMenuOpen }"></span>
                    <span :class="{ 'sv-burger-line--open': mobileMenuOpen }"></span>
                    <span :class="{ 'sv-burger-line--open': mobileMenuOpen }"></span>
                </button>
            </div>
        </header>

        <!-- Mobile Navigation Drawer -->
        <div class="sv-mobile-menu" :class="{ 'sv-mobile-menu--open': mobileMenuOpen }">
            <div class="sv-mobile-menu-content">
                <div class="sv-mobile-menu-header">
                    <div class="nd-brand">
                        <img v-if="siteSettings.logo_path" :src="'/storage/' + siteSettings.logo_path" class="w-8 h-8 object-contain rounded-md" alt="Logo" />
                        <div v-else class="nd-crest">SV</div>
                        <div class="nd-brand-text">
                            <div class="nd-school-name" style="font-size: 16px;">{{ siteSettings.institute_name }}</div>
                        </div>
                    </div>
                    <button @click="mobileMenuOpen = false" class="sv-close-btn">&times;</button>
                </div>
                <nav class="sv-mobile-nav">
                    <Link href="/" class="sv-mobile-link" @click="mobileMenuOpen = false">Home</Link>
                    <Link href="/#notices" class="sv-mobile-link" @click="mobileMenuOpen = false">Notice Board</Link>
                    <Link href="/#about" class="sv-mobile-link" @click="mobileMenuOpen = false">About Us</Link>
                    <Link href="/#programs" class="sv-mobile-link" @click="mobileMenuOpen = false">Programs</Link>
                    <Link href="/#campus-life" class="sv-mobile-link" @click="mobileMenuOpen = false">Campus Life</Link>
                    <Link href="/result" class="sv-mobile-link" @click="mobileMenuOpen = false">Results</Link>
                    <Link href="/#contact" class="sv-mobile-link" @click="mobileMenuOpen = false">Contact</Link>
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="sv-mobile-cta"
                        @click="mobileMenuOpen = false"
                    >Dashboard</Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="sv-mobile-cta"
                        @click="mobileMenuOpen = false"
                    >Staff Login</Link>
                </nav>
            </div>
        </div>

        <!-- Main Body -->
        <main class="nd-main">
            <article class="nd-article">
                <!-- Eyebrow Meta -->
                <div class="nd-meta">
                    <span class="nd-category-badge nd-cat--event">Campus Life</span>
                </div>

                <!-- Title -->
                <h1 class="nd-title">{{ item.title }}</h1>

                <!-- Featured Image -->
                <div v-if="item.image_path" class="nd-featured-image-wrapper animate-in fade-in duration-300">
                    <img :src="'/storage/' + item.image_path" class="nd-featured-image" :alt="item.title" />
                </div>

                <!-- Complete Description -->
                <div class="nd-description whitespace-pre-line">
                    {{ item.description || 'No description provided.' }}
                </div>

                <!-- Back button -->
                <div class="nd-actions-footer">
                    <Link href="/" class="nd-back-btn">
                        &larr; Back to Homepage
                    </Link>
                </div>
            </article>
        </main>
    </div>
</template>

<style>
/* Same premium styling as notices details for consistent aesthetics */
:root {
    --sv-forest: #1F4D3A;
    --sv-forest-dark: #143528;
    --sv-brass: #C89B3C;
    --sv-brass-dark: #A37B2A;
    --sv-ink: #14213D;
    --sv-card: #FDFDFB;
    --sv-bg: #F4F4F0;
    --sv-line: rgba(20,33,61,0.08);
    --sv-text-soft: #5A6578;
}

.nd-root {
    min-height: 100vh;
    background-color: var(--sv-bg);
    color: var(--sv-ink);
    font-family: 'Inter', sans-serif;
    position: relative;
}

.nd-texture {
    position: absolute;
    inset: 0;
    opacity: 0.05;
    background-image: radial-gradient(#14213d 1px, transparent 0), radial-gradient(#14213d 1px, transparent 0);
    background-size: 8px 8px;
    background-position: 0 0, 4px 4px;
    pointer-events: none;
}

.nd-header {
    background: #fff;
    border-bottom: 1px solid var(--sv-line);
    position: sticky;
    top: 0;
    z-index: 50;
}

.nd-topbar {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    height: 72px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.nd-brand {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.nd-crest {
    width: 38px;
    height: 38px;
    background: var(--sv-forest);
    color: #fff;
    font-weight: 800;
    font-size: 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}

.nd-brand-text {
    display: flex;
    flex-direction: column;
}

.nd-school-name {
    font-weight: 800;
    font-size: 15px;
    color: var(--sv-ink);
    line-height: 1.2;
}

.nd-school-tag {
    font-size: 10px;
    color: var(--sv-text-soft);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.nd-nav {
    display: flex;
    align-items: center;
    gap: 22px;
}

@media (max-width: 820px) {
    .nd-nav {
        display: none;
    }
}

.nd-nav-link {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--sv-text-soft);
    text-decoration: none;
    transition: color 0.15s;
}

.nd-nav-link:hover {
    color: var(--sv-forest);
}

.nd-nav-link--active {
    color: var(--sv-forest);
}

.nd-nav-cta {
    font-size: 13px;
    font-weight: 700;
    background: var(--sv-ink);
    color: #fff;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.15s;
}

.nd-nav-cta:hover {
    background: var(--sv-forest);
}

/* Burger Button */
.sv-burger-btn {
    display: none;
    flex-direction: column;
    justify-content: space-between;
    width: 22px;
    height: 16px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    z-index: 60;
}

@media (max-width: 820px) {
    .sv-burger-btn {
        display: flex;
    }
}

.sv-burger-btn span {
    width: 100%;
    height: 2px;
    background-color: var(--sv-ink);
    transition: all 0.25s ease-in-out;
}

.sv-burger-line--open:nth-child(1) {
    transform: translateY(7px) rotate(45deg);
}

.sv-burger-line--open:nth-child(2) {
    opacity: 0;
}

.sv-burger-line--open:nth-child(3) {
    transform: translateY(-7px) rotate(-45deg);
}

/* Mobile Menu */
.sv-mobile-menu {
    position: fixed;
    inset: 0;
    background: rgba(20, 33, 61, 0.4);
    backdrop-filter: blur(4px);
    z-index: 100;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease;
}

.sv-mobile-menu--open {
    opacity: 1;
    pointer-events: auto;
}

.sv-mobile-menu-content {
    width: 280px;
    height: 100%;
    background: #fff;
    padding: 24px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 24px;
    transform: translateX(-100%);
    transition: transform 0.25s ease;
}

.sv-mobile-menu--open .sv-mobile-menu-content {
    transform: translateX(0);
}

.sv-mobile-menu-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.sv-close-btn {
    background: transparent;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: var(--sv-ink);
}

.sv-mobile-nav {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.sv-mobile-link {
    font-size: 15px;
    font-weight: 600;
    color: var(--sv-text-soft);
    text-decoration: none;
}

.sv-mobile-link:hover {
    color: var(--sv-forest);
}

.sv-mobile-cta {
    margin-top: 12px;
    display: block;
    text-align: center;
    padding: 10px;
    background: var(--sv-brass);
    color: var(--sv-ink);
    border-radius: 8px;
    font-weight: 700;
    text-decoration: none;
}

.nd-main {
    max-width: 800px;
    margin: 0 auto;
    padding: 48px 24px;
}

.nd-article {
    background: #fff;
    border: 1px solid var(--sv-line);
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 10px 30px -15px rgba(20, 33, 61, 0.1);
    position: relative;
    overflow: hidden;
}

@media (max-width: 600px) {
    .nd-article {
        padding: 24px 20px;
    }
}

.nd-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
}

.nd-category-badge {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 4px 10px;
    border-radius: 99px;
    background: rgba(31, 77, 58, 0.08);
    color: var(--sv-forest);
}

.nd-title {
    font-size: clamp(24px, 4.5vw, 32px);
    font-weight: 800;
    line-height: 1.25;
    margin: 0 0 24px;
    color: var(--sv-ink);
}

.nd-featured-image-wrapper {
    width: 100%;
    max-height: 420px;
    overflow: hidden;
    border-radius: 12px;
    margin-bottom: 28px;
    border: 1px solid var(--sv-line);
    background: #f8f9fa;
}

.nd-featured-image {
    width: 100%;
    height: auto;
    max-height: 420px;
    object-fit: contain;
}

.nd-description {
    font-size: 15.5px;
    line-height: 1.85;
    color: #2c3e50;
    margin-bottom: 32px;
    text-align: justify;
}

.nd-actions-footer {
    border-top: 1px solid var(--sv-line);
    padding-top: 24px;
    margin-top: 32px;
}

.nd-back-btn {
    display: inline-flex;
    align-items: center;
    font-size: 14px;
    font-weight: 700;
    color: var(--sv-forest);
    text-decoration: none;
    transition: transform 0.2s;
}

.nd-back-btn:hover {
    transform: translateX(-4px);
    color: var(--sv-forest-dark);
}
</style>
