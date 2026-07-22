<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Notice {
    id: number;
    title: string;
    slug: string;
    description: string;
    category: 'exam' | 'holiday' | 'event' | 'general' | 'admission' | 'urgent';
    attachment_path: string | null;
    image_path: string | null;
    publish_date: string;
    expiry_date: string | null;
    target_audience: 'all' | 'students' | 'teachers' | 'parents';
    status: 'active' | 'inactive' | 'draft';
    posted_by: string;
}

const props = defineProps<{
    notice: Notice;
}>();

const mobileMenuOpen = ref(false);

const page = usePage();
const siteSettings = computed(() => page.props.site_settings as {
    institute_name: string;
    tagline: string;
    logo_path: string | null;
    favicon_path: string | null;
});

function formatDate(dateStr: string): string {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

const categoryLabels: Record<string, string> = {
    general: 'General Circular',
    exam: 'Exam Schedule',
    holiday: 'Holiday Notice',
    event: 'School Event',
    admission: 'Admissions Notice',
    urgent: 'Urgent Announcement',
};

const excerpt = computed(() => {
    if (!props.notice.description) return '';
    return props.notice.description.substring(0, 160).replace(/\s+/g, ' ').trim() + '...';
});
</script>

<template>
    <Head>
        <title>{{ notice.title }} — {{ siteSettings.institute_name || 'Ideal Professional Institute' }}</title>
        <meta name="description" :content="excerpt || 'Notice details page.'" />
        <meta name="keywords" content="Ideal Professional Institute, Notices, Announcements, School Circulars" />
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
                    <Link href="/#notices" class="nd-nav-link nd-nav-link--active">Notice Board</Link>
                    <Link href="/#about" class="nd-nav-link">About Us</Link>
                    <Link href="/#programs" class="nd-nav-link">Programs</Link>
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
                    <span
                        class="nd-category-badge"
                        :class="'nd-cat--' + notice.category"
                    >
                        {{ categoryLabels[notice.category] || notice.category }}
                    </span>
                    <span class="nd-meta-divider">·</span>
                    <span class="nd-date">{{ formatDate(notice.publish_date) }}</span>
                </div>

                <!-- Title -->
                <h1 class="nd-title">{{ notice.title }}</h1>

                <!-- Featured Image -->
                <div v-if="notice.image_path" class="nd-featured-image-wrapper animate-in fade-in duration-300">
                    <img :src="'/storage/' + notice.image_path" class="nd-featured-image" :alt="notice.title" />
                </div>

                <!-- Complete Description -->
                <div class="nd-description whitespace-pre-line">
                    {{ notice.description }}
                </div>

                <!-- Attachments Section -->
                <div v-if="notice.attachment_path" class="nd-attachment-section">
                    <h3>📎 Notice Attachment</h3>
                    <div class="nd-attachment-card">
                        <div class="nd-attachment-info">
                            <span class="nd-file-icon">📄</span>
                            <div>
                                <div class="nd-file-name">Official Notice Document</div>
                                <div class="nd-file-meta">PDF or Image Attachment</div>
                            </div>
                        </div>
                        <a
                            :href="'/storage/' + notice.attachment_path"
                            download
                            target="_blank"
                            class="nd-download-btn"
                        >
                            📥 Download File
                        </a>
                    </div>
                </div>

                <!-- Back button -->
                <div class="nd-actions-footer">
                    <Link href="/" class="nd-back-btn">
                        &larr; Back to Notice Board
                    </Link>
                </div>
            </article>
        </main>

        <!-- Footer -->
        <footer class="nd-footer">
            <div class="nd-footer-inner">
                <div><strong>{{ siteSettings.institute_name }}</strong> · {{ siteSettings.tagline }}</div>
                <div>© 2026 · All rights reserved · Developed By <a href="https://crownsit.com" target="_blank" rel="noopener" style="color:var(--sv-brass);text-decoration:underline">Crowns IT</a></div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap');

.nd-root {
    --sv-ink: #14213D;
    --sv-paper: #EEF1E9;
    --sv-card: #FBFAF5;
    --sv-forest: #1F4D3A;
    --sv-forest-dark: #173A2C;
    --sv-brass: #C89B3C;
    --sv-brass-dark: #A87F2B;
    --sv-urgent: #B33A3A;
    --sv-line: rgba(20,33,61,0.12);
    --sv-text-soft: rgba(20,33,61,0.62);

    font-family: 'Inter', sans-serif;
    background: var(--sv-paper);
    color: var(--sv-ink);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    position: relative;
}

.nd-texture {
    position: fixed;
    inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(20,33,61,0.035) 1px, transparent 0);
    background-size: 22px 22px;
    pointer-events: none;
    z-index: 0;
}

/* Header */
.nd-header {
    background: var(--sv-ink);
    color: #fff;
    border-bottom: 3px solid var(--sv-brass);
    z-index: 50;
}
.nd-topbar {
    max-width: 1000px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
}
.nd-brand { display: flex; align-items: center; gap: 12px; }
.nd-crest {
    width: 44px; height: 44px; border-radius: 50%;
    background: radial-gradient(circle at 35% 30%, var(--sv-brass) 0%, var(--sv-brass-dark) 70%);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Fraunces', serif; font-weight: 700; color: var(--sv-ink); font-size: 18px;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(200,155,60,0.25);
}
.nd-brand-text { display: flex; flex-direction: column; }
.nd-school-name { font-size: 19px; font-weight: 600; line-height: 1.15; }
.nd-school-tag { font-size: 11.5px; color: rgba(255,255,255,0.6); font-family: 'IBM Plex Mono', monospace; letter-spacing: 0.5px; margin-top: 2px; }
.nd-nav { display: flex; gap: 20px; align-items: center; }
.nd-nav-link { font-size: 14px; font-weight: 500; color: rgba(255,255,255,0.75); padding: 4px 0; position: relative; transition: color .2s; }
.nd-nav-link:hover { color: #fff; }
.nd-nav-link--active { color: var(--sv-brass); }
.nd-nav-link--active::after { content: ''; position: absolute; left: 0; right: 0; bottom: -6px; height: 2px; background: var(--sv-brass); }
.nd-nav-cta {
    padding: 8px 18px; background: var(--sv-brass); color: var(--sv-ink);
    border-radius: 999px; font-size: 13px; font-weight: 700; transition: background .2s;
}
.nd-nav-cta:hover { background: var(--sv-brass-dark); color: #fff; }
@media (max-width: 760px) { .nd-nav { display: none; } }

/* Main */
.nd-main {
    flex: 1;
    max-width: 800px;
    margin: 48px auto;
    width: 100%;
    padding: 0 24px;
    box-sizing: border-box;
    z-index: 10;
}

.nd-article {
    background: var(--sv-card);
    border: 1px solid var(--sv-line);
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 10px 30px -15px rgba(20,33,61,0.15);
}

@media (max-width: 600px) {
    .nd-article {
        padding: 24px;
    }
}

.nd-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
}

.nd-category-badge {
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.nd-cat--urgent { color: var(--sv-urgent); }
.nd-cat--exam { color: #3B5FA0; }
.nd-cat--holiday { color: var(--sv-forest); }
.nd-cat--event { color: #7A4FA0; }
.nd-cat--admission { color: var(--sv-brass-dark); }
.nd-cat--general { color: var(--sv-ink); }

.nd-meta-divider {
    color: var(--sv-text-soft);
}

.nd-date {
    color: var(--sv-text-soft);
}

.nd-title {
    font-family: 'Fraunces', serif;
    font-size: clamp(22px, 5vw, 32px);
    font-weight: 700;
    line-height: 1.3;
    margin: 0 0 24px;
    color: var(--sv-ink);
}

/* Featured Image */
.nd-featured-image-wrapper {
    margin-bottom: 28px;
    border-radius: 12px;
    overflow: hidden;
    max-height: 400px;
    border: 1px solid var(--sv-line);
}

.nd-featured-image {
    width: 100%;
    height: 100%;
    object-cover: cover;
}

.nd-description {
    font-size: 16px;
    line-height: 1.8;
    color: rgba(20,33,61,0.85);
    margin-bottom: 36px;
}

/* Attachment card */
.nd-attachment-section {
    margin-top: 36px;
    padding-top: 28px;
    border-top: 1px dashed var(--sv-line);
}

.nd-attachment-section h3 {
    font-size: 15px;
    font-weight: 700;
    margin: 0 0 12px;
    color: var(--sv-ink);
}

.nd-attachment-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(31,77,58,0.04);
    border: 1px solid rgba(31,77,58,0.12);
    border-radius: 10px;
    padding: 16px 20px;
    gap: 16px;
    flex-wrap: wrap;
}

.nd-attachment-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.nd-file-icon {
    font-size: 24px;
}

.nd-file-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--sv-ink);
}

.nd-file-meta {
    font-size: 12px;
    color: var(--sv-text-soft);
}

.nd-download-btn {
    font-size: 13px;
    font-weight: 700;
    color: var(--sv-forest);
    border: 1.5px solid var(--sv-forest);
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.2s;
    background: transparent;
    cursor: pointer;
}

.nd-download-btn:hover {
    background: var(--sv-forest);
    color: #fff;
}

.nd-actions-footer {
    margin-top: 36px;
    padding-top: 20px;
    border-top: 1px solid var(--sv-line);
}

.nd-back-btn {
    font-size: 14px;
    font-weight: 600;
    color: var(--sv-forest);
    transition: color 0.2s;
}

.nd-back-btn:hover {
    color: var(--sv-brass-dark);
}

/* Burger button */
.sv-burger-btn {
    display: none;
    flex-direction: column;
    justify-content: space-between;
    width: 24px;
    height: 18px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    z-index: 60;
}
.sv-burger-btn span {
    width: 100%;
    height: 2px;
    background-color: #fff;
    transition: all 0.3s ease;
}
@media (max-width: 760px) {
    .sv-burger-btn {
        display: flex;
    }
}
.sv-burger-line--open:nth-child(1) {
    transform: translateY(8px) rotate(45deg);
}
.sv-burger-line--open:nth-child(2) {
    opacity: 0;
}
.sv-burger-line--open:nth-child(3) {
    transform: translateY(-8px) rotate(-45deg);
}

/* Mobile Drawer Overlay */
.sv-mobile-menu {
    position: fixed;
    inset: 0;
    background: rgba(20, 33, 61, 0.5);
    z-index: 100;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}
.sv-mobile-menu--open {
    opacity: 1;
    pointer-events: auto;
}
.sv-mobile-menu-content {
    position: absolute;
    top: 0;
    right: 0;
    width: 280px;
    height: 100%;
    background: var(--sv-ink);
    color: #fff;
    padding: 24px;
    box-shadow: -5px 0 25px rgba(0,0,0,0.3);
    transform: translateX(100%);
    transition: transform 0.3s ease;
    display: flex;
    flex-direction: column;
    gap: 24px;
}
.sv-mobile-menu--open .sv-mobile-menu-content {
    transform: translateX(0);
}
.sv-mobile-menu-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 16px;
}
.sv-close-btn {
    font-size: 28px;
    background: transparent;
    border: none;
    color: #fff;
    cursor: pointer;
    line-height: 1;
}
.sv-mobile-nav {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.sv-mobile-link {
    font-size: 16px;
    font-weight: 500;
    color: rgba(255,255,255,0.8);
    transition: color 0.2s;
    padding: 8px 0;
}
.sv-mobile-link:hover {
    color: var(--sv-brass);
}
.sv-mobile-cta {
    margin-top: 16px;
    display: block;
    text-align: center;
    padding: 10px;
    background: var(--sv-brass);
    color: var(--sv-ink);
    border-radius: 8px;
    font-weight: 700;
    transition: background 0.2s;
}
.sv-mobile-cta:hover {
    background: var(--sv-brass-dark);
    color: #fff;
}

/* Footer */
.nd-footer {
    background: #000;
    color: rgba(255,255,255,0.7);
    padding: 24px;
    margin-top: auto;
}
.nd-footer-inner {
    max-width: 1000px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    font-size: 12.5px;
}
.nd-footer-inner strong { color: #fff; }
</style>
