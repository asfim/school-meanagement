<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const siteSettings = computed(() => page.props.site_settings as {
    institute_name: string;
    tagline: string;
    logo_path: string | null;
    favicon_path: string | null;
});
import FlashToast from '@/components/FlashToast.vue';

interface Banner {
    id: number;
    title: string;
    subtitle: string | null;
    paragraph: string | null;
    button_text: string | null;
    button_url: string | null;
    bg_color: 'forest' | 'ink' | 'brass';
    image_path: string | null;
    sort_order: number;
    is_active: boolean;
}

interface Notice {
    id: number;
    title: string;
    description: string;
    category: 'exam' | 'holiday' | 'event' | 'general' | 'admission' | 'urgent';
    publish_date: string;
    expiry_date: string | null;
    target_audience: 'all' | 'students' | 'teachers' | 'parents';
    status: 'active' | 'inactive' | 'draft';
    posted_by: string;
    is_pinned?: boolean;
    has_attachment?: boolean;
}

interface Student {
    student_id: string;
    full_name_en: string;
    class: string;
    section: string;
    roll_number: number;
}

interface ExamResult {
    exam_name: string;
    marks: Record<string, number>;
    gpa: string;
    grade: string;
    pass_status: 'pass' | 'fail';
    remarks: string | null;
}

interface FeePayment {
    fee_month: string;
    amount_due: string;
    amount_paid: string;
    discount: string;
    status: 'paid' | 'partial' | 'unpaid';
    receipt_number: string | null;
    payment_date: string | null;
}

interface CampusLifeItem {
    id: number;
    title: string;
    description: string | null;
    image_path: string | null;
    sort_order: number;
    is_active: boolean;
}

interface Subject {
    id: number;
    name: string;
    code: string;
}

interface ProgramItem {
    id: number;
    name: string;
    code: string | null;
    description: string | null;
    duration_years: number | null;
    subjects: Subject[];
}

const props = defineProps<{
    banners: Banner[];
    notices: Notice[];
    campusLifeItems: CampusLifeItem[];
    programs: ProgramItem[];
    resultData: { student: Student; result: ExamResult } | null;
    resultError: string | null;
    feeData: { student: Student; payments: FeePayment[] } | null;
    feeError: string | null;
    filters: {
        result_student_id?: string;
        result_exam_name?: string;
        fee_student_id?: string;
    };
}>();

const expandedProgramId = ref<number | null>(null);
function toggleProgram(id: number) {
    expandedProgramId.value = expandedProgramId.value === id ? null : id;
}

// ── Banner slider ──────────────────────────────────────────────────────────
const sliderIndex = ref(0);
const isTransitioning = ref(false);
let sliderTimer: ReturnType<typeof setInterval> | null = null;

const bgColors: Record<string, string> = {
    forest: 'linear-gradient(180deg, #1F4D3A 0%, #173A2C 100%)',
    ink:    'linear-gradient(180deg, #14213D 0%, #0b1526 100%)',
    brass:  'linear-gradient(180deg, #8a6520 0%, #5c4210 100%)',
};

const activeBanners = computed(() => props.banners.length > 0 ? props.banners : [
    {
        id: 0,
        title: 'All school announcements, in one place — always up to date.',
        subtitle: '',
        paragraph: 'From exam schedules to holidays, admissions, or urgent announcements — every notice published from the admin panel appears here automatically.',
        button_text: 'View Notices',
        button_url: '#notices',
        bg_color: 'forest' as const,
        sort_order: 0,
        is_active: true,
    }
]);

function goToSlide(index: number) {
    if (isTransitioning.value) return;
    isTransitioning.value = true;
    setTimeout(() => {
        sliderIndex.value = index;
        isTransitioning.value = false;
    }, 250);
}

function nextSlide() {
    goToSlide((sliderIndex.value + 1) % activeBanners.value.length);
}

function prevSlide() {
    goToSlide((sliderIndex.value - 1 + activeBanners.value.length) % activeBanners.value.length);
}

onMounted(() => {
    if (activeBanners.value.length > 1) {
        sliderTimer = setInterval(nextSlide, 5000);
    }
});

onUnmounted(() => {
    if (sliderTimer) clearInterval(sliderTimer);
});

// ── Notice board state ──────────────────────────────────────────────────────
const CATS = ['All', 'Exam', 'Holiday', 'Event', 'General', 'Admission', 'Urgent'] as const;
type Cat = (typeof CATS)[number];

const activeCat = ref<Cat>('All');
const searchTerm = ref('');

const filteredNotices = computed(() => {
    let list = [...props.notices];

    if (activeCat.value !== 'All') {
        list = list.filter((n) => n.category.toLowerCase() === activeCat.value.toLowerCase());
    }

    if (searchTerm.value.trim()) {
        const t = searchTerm.value.trim().toLowerCase();
        list = list.filter((n) => n.title.toLowerCase().includes(t) || n.description.toLowerCase().includes(t));
    }

    list.sort((a, b) => {
        if ((a.is_pinned ? 1 : 0) !== (b.is_pinned ? 1 : 0)) return a.is_pinned ? -1 : 1;
        return new Date(b.publish_date).getTime() - new Date(a.publish_date).getTime();
    });

    return list;
});

function catColor(cat: string): string {
    const map: Record<string, string> = {
        exam: '#3B5FA0',
        holiday: '#1F4D3A',
        event: '#A87F2B',
        general: '#6B6156',
        admission: '#7A4FA0',
        urgent: '#B33A3A',
    };
    return map[cat.toLowerCase()] ?? '#6B6156';
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

// ── Portals tab ─────────────────────────────────────────────────────────────
const activeTab = ref<'notices' | 'results' | 'fees'>('notices');

const resultForm = useForm({
    result_student_id: props.filters.result_student_id || '',
    result_exam_name: props.filters.result_exam_name || 'First Term Exam',
});

const feeForm = useForm({
    fee_student_id: props.filters.fee_student_id || '',
});

function submitResultQuery() {
    resultForm.get('/', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { activeTab.value = 'results'; },
    });
}

function submitFeeQuery() {
    feeForm.get('/', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { activeTab.value = 'fees'; },
    });
}

function printCard(elementId: string) {
    const printContent = document.getElementById(elementId)?.innerHTML;
    const originalContent = document.body.innerHTML;
    if (printContent) {
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        window.location.reload();
    }
}

function getSubjectGp(score: number): number {
    if (score >= 80) return 5.0;
    if (score >= 70) return 4.0;
    if (score >= 60) return 3.5;
    if (score >= 50) return 3.0;
    if (score >= 40) return 2.0;
    if (score >= 33) return 1.0;
    return 0.0;
}

function getSubjectGrade(score: number): string {
    if (score >= 80) return 'A+';
    if (score >= 70) return 'A';
    if (score >= 60) return 'A-';
    if (score >= 50) return 'B';
    if (score >= 40) return 'C';
    if (score >= 33) return 'D';
    return 'F';
}
</script>

<template>
    <Head title="Saraswati Vidyaniketan — Notice Board" />
    <FlashToast />

    <div class="sv-root">
        <!-- Paper texture overlay -->
        <div class="sv-paper-texture" aria-hidden="true"></div>

        <!-- ── Header ──────────────────────────────────────────────── -->
        <header class="sv-header">
            <div class="sv-topbar">
                <div class="sv-brand">
                    <img v-if="siteSettings.logo_path" :src="'/storage/' + siteSettings.logo_path" class="w-10 h-10 object-contain rounded-md mr-2" alt="Logo" />
                    <div v-else class="sv-crest">SV</div>
                    <div class="sv-brand-text">
                        <div class="sv-school-name">{{ siteSettings.institute_name }}</div>
                        <div class="sv-school-tag">{{ siteSettings.tagline }}</div>
                    </div>
                </div>
                <nav class="sv-nav">
                    <a href="#" class="sv-nav-link sv-nav-link--active">Home</a>
                    <a href="#notices" class="sv-nav-link">Notice Board</a>
                    <a href="#about" class="sv-nav-link">About Us</a>
                    <a href="#programs" class="sv-nav-link">Programs</a>
                    <Link href="/result" class="sv-nav-link">Results</Link>
                    <a href="#contact" class="sv-nav-link">Contact</a>
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="sv-nav-cta"
                    >Dashboard</Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="sv-nav-cta"
                    >Staff Login</Link>
                </nav>
            </div>
        </header>

        <!-- ── Hero Banner Slider ─────────────────────────────────── -->
        <section class="sv-hero">
            <!-- Slides -->
            <div
                v-for="(banner, index) in activeBanners"
                :key="banner.id || index"
                class="sv-slide"
                :class="{ 'sv-slide--active': sliderIndex === index }"
                :style="banner.image_path
                    ? { backgroundImage: `url('/storage/${banner.image_path}')`, backgroundSize: 'cover', backgroundPosition: 'center' }
                    : { background: bgColors[banner.bg_color ?? 'forest'] }"
            >
                <!-- Gradient overlay when image is present -->
                <div
                    v-if="banner.image_path"
                    class="sv-hero-img-overlay"
                    aria-hidden="true"
                ></div>

                <!-- Decorative circle (only when no image) -->
                <div
                    v-if="!banner.image_path"
                    class="sv-hero-deco"
                    aria-hidden="true"
                ></div>

                <div class="sv-hero-inner w-full">
                    <div class="sv-slide-content">
                        <div class="sv-eyebrow">Notice Board · Public Portal</div>
                        <h1>{{ banner.title }}</h1>
                        <p v-if="banner.subtitle" class="sv-slide-subtitle">
                            {{ banner.subtitle }}
                        </p>
                        <p v-if="banner.paragraph" class="sv-hero-sub">
                            {{ banner.paragraph }}
                        </p>
                        <a
                            v-if="banner.button_text"
                            :href="banner.button_url ?? '#notices'"
                            class="sv-slide-btn"
                        >{{ banner.button_text }} →</a>
                    </div>
                </div>
            </div>

            <!-- Slider Controls (positioned absolute to overlay on active slide) -->
            <div v-if="activeBanners.length > 1" class="sv-slider-controls-overlay">
                <div class="sv-hero-inner">
                    <div class="sv-slider-controls">
                        <button class="sv-slider-arrow" @click.stop="prevSlide" aria-label="Previous banner">‹</button>
                        <div class="sv-slider-dots">
                            <button
                                v-for="(_, i) in activeBanners"
                                :key="i"
                                class="sv-dot"
                                :class="{ 'sv-dot--active': sliderIndex === i }"
                                @click.stop="goToSlide(i)"
                                :aria-label="`Slide ${i + 1}`"
                            ></button>
                        </div>
                        <button class="sv-slider-arrow" @click.stop="nextSlide" aria-label="Next banner">›</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Filter / Search Controls ───────────────────────────── -->
        <div id="notices" class="sv-controls-wrap">
            <div class="sv-controls">
                <div class="sv-filters">
                    <button
                        v-for="cat in CATS"
                        :key="cat"
                        class="sv-filter-btn"
                        :class="{ 'sv-filter-btn--active': activeCat === cat }"
                        @click="activeCat = cat"
                    >{{ cat }}</button>
                </div>
                <div class="sv-search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input
                        v-model="searchTerm"
                        type="text"
                        placeholder="Search notices..."
                        aria-label="Search notices"
                    />
                </div>
            </div>
        </div>

        <!-- ── Notice Grid ─────────────────────────────────────────── -->
        <main class="sv-main">
            <div class="sv-section-label">
                <h2>Latest Notices</h2>
                <span>{{ filteredNotices.length }} notices found</span>
            </div>

            <div class="sv-notice-grid">
                <!-- Empty state -->
                <div v-if="filteredNotices.length === 0" class="sv-empty-state">
                    <div class="sv-empty-emoji">📌</div>
                    No notices found at this time.
                </div>

                <div
                    v-for="(notice, i) in filteredNotices"
                    :key="notice.id"
                    class="sv-notice-card"
                    :class="{ 'sv-notice-card--urgent': notice.category === 'urgent' }"
                    :data-index="i % 3"
                >
                    <div class="sv-pin"></div>
                    <div
                        class="sv-tape"
                        :style="{ background: catColor(notice.category) }"
                    >{{ notice.category }}</div>
                    <div class="sv-notice-meta">
                        <span>{{ formatDate(notice.publish_date) }}</span>
                        <span v-if="notice.is_pinned">· Pinned</span>
                    </div>
                    <h3>{{ notice.title }}</h3>
                    <p class="sv-notice-excerpt">{{ notice.description }}</p>
                    <div class="sv-notice-footer">
                        <span class="sv-notice-by">Posted by: {{ notice.posted_by || 'Admin Office' }}</span>
                        <span v-if="notice.has_attachment" class="sv-attach-badge">📎 Attachment</span>
                    </div>
                </div>
            </div>

            <!-- ── Student / Fee Portals ───────────────────────────── -->
            <div class="sv-portals" id="portals">
                <div class="sv-section-label" style="margin-top: 56px;">
                    <h2>Student Self-Service Portals</h2>
                    <span>Results &amp; Tuition Inquiry</span>
                </div>

                <!-- Portal tabs -->
                <div class="sv-portal-tabs">
                    <button
                        class="sv-ptab"
                        :class="{ 'sv-ptab--active': activeTab === 'results' }"
                        @click="activeTab = 'results'"
                    >📋 Result Portal</button>
                    <button
                        class="sv-ptab"
                        :class="{ 'sv-ptab--active': activeTab === 'fees' }"
                        @click="activeTab = 'fees'"
                    >💰 Fee Inquiry</button>
                </div>

                <!-- Result Portal -->
                <div v-if="activeTab === 'results'" class="sv-portal-grid">
                    <div class="sv-portal-panel">
                        <h3 class="sv-portal-panel-title">Lookup Report Card</h3>
                        <form @submit.prevent="submitResultQuery" class="sv-portal-form">
                            <div>
                                <label class="sv-form-label">Student ID *</label>
                                <input
                                    v-model="resultForm.result_student_id"
                                    type="text"
                                    placeholder="STU-2026-0001"
                                    class="sv-form-input"
                                />
                            </div>
                            <div>
                                <label class="sv-form-label">Select Exam *</label>
                                <select v-model="resultForm.result_exam_name" class="sv-form-input">
                                    <option value="First Term Exam">First Term Exam</option>
                                    <option value="Midterm Exam">Midterm Exam</option>
                                    <option value="Annual Exam">Annual Exam</option>
                                </select>
                            </div>
                            <button type="submit" :disabled="resultForm.processing" class="sv-form-btn">
                                Search Result
                            </button>
                        </form>
                        <div v-if="resultError" class="sv-form-error">{{ resultError }}</div>
                    </div>

                    <div class="sv-portal-result">
                        <div v-if="resultData" class="sv-result-card">
                            <div class="sv-result-card-header">
                                <h4>Result Details</h4>
                                <button @click="printCard('printable-report-card')" class="sv-print-btn">🖨 Print Report Card</button>
                            </div>
                            <div id="printable-report-card" class="sv-marksheet">
                                <div class="sv-marksheet-header">
                                    <h2>{{ siteSettings.institute_name }}</h2>
                                    <p>Student Academic Mark Sheet</p>
                                </div>
                                <div class="sv-marksheet-bio">
                                    <div>
                                        <div>Student: <strong>{{ resultData.student.full_name_en }}</strong></div>
                                        <div>ID: <strong>{{ resultData.student.student_id }}</strong></div>
                                        <div>Program: <strong>{{ resultData.student.program_name }}</strong></div>
                                    </div>
                                    <div style="text-align:right">
                                        <div>Exam: <strong>{{ resultData.result.exam_name }}</strong></div>
                                        <div>Roll: <strong>{{ resultData.student.roll_number }}</strong></div>
                                        <div>
                                            Status:
                                            <span
                                                class="sv-pass-badge"
                                                :class="resultData.result.pass_status === 'pass' ? 'sv-pass-badge--pass' : 'sv-pass-badge--fail'"
                                            >{{ resultData.result.pass_status.toUpperCase() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <table class="sv-table">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th style="text-align:center">Score</th>
                                            <th style="text-align:center">GP</th>
                                            <th style="text-align:center">Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(score, subject) in resultData.result.marks" :key="subject">
                                            <td>{{ subject }}</td>
                                            <td style="text-align:center;font-weight:700">{{ score }}</td>
                                            <td style="text-align:center;font-family:monospace">{{ getSubjectGp(score).toFixed(2) }}</td>
                                            <td style="text-align:center;font-weight:700" :style="{ color: score < 33 ? '#B33A3A' : 'inherit' }">{{ getSubjectGrade(score) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="sv-marksheet-summary">
                                    <div>Remarks: <em>{{ resultData.result.remarks || 'Satisfactory.' }}</em></div>
                                    <div style="text-align:right">
                                        <div>GPA: <strong style="font-size:1.1em">{{ resultData.result.gpa }}</strong></div>
                                        <div>Final Grade: <strong style="font-size:1.25em">{{ resultData.result.grade }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="sv-portal-placeholder">
                            Enter a Student ID above to view the report card.
                        </div>
                    </div>
                </div>

                <!-- Fee Portal -->
                <div v-if="activeTab === 'fees'" class="sv-portal-grid">
                    <div class="sv-portal-panel">
                        <h3 class="sv-portal-panel-title">Tuition Fee Inquiry</h3>
                        <form @submit.prevent="submitFeeQuery" class="sv-portal-form">
                            <div>
                                <label class="sv-form-label">Student ID *</label>
                                <input
                                    v-model="feeForm.fee_student_id"
                                    type="text"
                                    placeholder="STU-2026-0001"
                                    class="sv-form-input"
                                />
                            </div>
                            <button type="submit" :disabled="feeForm.processing" class="sv-form-btn">
                                Query Billings
                            </button>
                        </form>
                        <div v-if="feeError" class="sv-form-error">{{ feeError }}</div>
                    </div>

                    <div class="sv-portal-result">
                        <div v-if="feeData" class="sv-result-card">
                            <div class="sv-result-card-header">
                                <div>
                                    <h4>Tuition Statement</h4>
                                    <p style="font-size:12px;color:var(--sv-text-soft)">{{ feeData.student.full_name_en }} ({{ feeData.student.student_id }})</p>
                                </div>
                                <button @click="printCard('printable-fee-statement')" class="sv-print-btn">🖨 Print Statement</button>
                            </div>
                            <div id="printable-fee-statement" class="sv-marksheet">
                                <div class="sv-marksheet-header">
                                    <h2>{{ siteSettings.institute_name }}</h2>
                                    <p>Tuition Fee Statement Ledger</p>
                                </div>
                                <table class="sv-table">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th style="text-align:center">Due</th>
                                            <th style="text-align:center">Paid</th>
                                            <th style="text-align:center">Discount</th>
                                            <th style="text-align:center">Status</th>
                                            <th style="text-align:right">Receipt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="pay in feeData.payments" :key="pay.fee_month">
                                            <td>{{ pay.fee_month }}</td>
                                            <td style="text-align:center;font-weight:700">৳{{ parseFloat(pay.amount_due).toFixed(2) }}</td>
                                            <td style="text-align:center;color:#1F4D3A;font-weight:600">৳{{ parseFloat(pay.amount_paid).toFixed(2) }}</td>
                                            <td style="text-align:center;color:#A87F2B">৳{{ parseFloat(pay.discount).toFixed(2) }}</td>
                                            <td style="text-align:center">
                                                <span v-if="pay.status === 'paid'" style="color:#1F4D3A;font-weight:700;font-size:11px">Paid</span>
                                                <span v-else-if="pay.status === 'partial'" style="color:#3B5FA0;font-weight:700;font-size:11px">Partial</span>
                                                <span v-else style="color:#B33A3A;font-weight:700;font-size:11px">Overdue</span>
                                            </td>
                                            <td style="text-align:right;font-family:monospace;font-size:11px">{{ pay.receipt_number || '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else class="sv-portal-placeholder">
                            Enter a Student ID above to view the tuition statement.
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- ── About ───────────────────────────────────────────────── -->
        <section class="sv-about" id="about">
            <div class="sv-about-inner">
                <div class="sv-about-text">
                    <div class="sv-eyebrow sv-eyebrow--dark">About Us</div>
                    <h2>40 Years of Education, Discipline &amp; Values</h2>
                    <p>Since 1986, Saraswati Vidyaniketan has been shaping the future of thousands of students in Dhaka. We believe true education means nurturing the hidden potential within every child with the right care and guidance.</p>
                </div>
                <div class="sv-stat-grid">
                    <div class="sv-stat-box"><div class="sv-stat-num">1850+</div><div class="sv-stat-label">Students</div></div>
                    <div class="sv-stat-box"><div class="sv-stat-num">96</div><div class="sv-stat-label">Teachers</div></div>
                    <div class="sv-stat-box"><div class="sv-stat-num">98%</div><div class="sv-stat-label">Pass Rate</div></div>
                    <div class="sv-stat-box"><div class="sv-stat-num">40+</div><div class="sv-stat-label">Years of Experience</div></div>
                </div>
            </div>
        </section>

        <!-- ── Academic Programs ───────────────────────────────────── -->
        <section class="sv-programs" id="programs">
            <div class="sv-programs-inner">
                <div class="sv-section-label">
                    <h2>Academic Programs</h2>
                    <span>Click a program to view its subjects</span>
                </div>
                <div v-if="programs.length === 0" class="sv-empty-state" style="grid-column:1/-1">
                    No academic programs have been added yet. Admin can add them from the dashboard.
                </div>
                <div v-else class="sv-program-grid">
                    <div
                        v-for="prog in programs"
                        :key="prog.id"
                        class="sv-program-card"
                        :class="{ 'sv-program-card--expanded': expandedProgramId === prog.id }"
                        @click="toggleProgram(prog.id)"
                        style="cursor:pointer"
                    >
                        <div class="sv-program-tag">{{ prog.code || 'PROG' }}</div>
                        <h3>{{ prog.name }}</h3>
                        <p v-if="prog.description">{{ prog.description }}</p>
                        <p v-if="prog.duration_years" style="font-size:12px;opacity:.6;margin-top:4px">Duration: {{ prog.duration_years }} year{{ prog.duration_years > 1 ? 's' : '' }}</p>

                        <!-- Subjects Dropdown -->
                        <div v-if="expandedProgramId === prog.id && prog.subjects.length > 0" class="sv-subject-list" @click.stop>
                            <div class="sv-subject-list-title">Subjects ({{ prog.subjects.length }})</div>
                            <ul>
                                <li v-for="sub in prog.subjects" :key="sub.id">
                                    <span class="sv-subject-code">{{ sub.code }}</span>
                                    <span>{{ sub.name }}</span>
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="expandedProgramId === prog.id && prog.subjects.length === 0" class="sv-subject-list" @click.stop>
                            <div style="text-align:center;padding:8px;font-size:12px;color:rgba(255,255,255,.5)">No subjects configured yet.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Campus Gallery ─────────────────────────────────────── -->
        <section class="sv-gallery" id="campus-life">
            <div class="sv-programs-inner">
                <div class="sv-section-label">
                    <h2>Campus Life</h2>
                    <span>Moments at a glance</span>
                </div>
                <div v-if="campusLifeItems.length === 0" class="sv-empty-state" style="grid-column:1/-1">
                    No gallery items yet. Admin can add them from the dashboard.
                </div>
                <div class="sv-gallery-grid" v-else>
                    <div
                        v-for="(item, i) in campusLifeItems"
                        :key="item.id"
                        class="sv-gtile"
                        :class="['sv-g' + (i + 1), { 'sv-has-image': item.image_path }]"
                        :style="item.image_path
                            ? { backgroundImage: `url('/storage/${item.image_path}')`, backgroundSize: 'cover', backgroundPosition: 'center' }
                            : {}"
                    >
                        <span>{{ item.title }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Contact ─────────────────────────────────────────────── -->
        <section class="sv-contact" id="contact">
            <div class="sv-contact-inner">
                <div>
                    <div class="sv-eyebrow">Contact</div>
                    <h2>Stay Connected With Us</h2>
                    <p class="sv-contact-line">📍 124, Mirpur Road, Dhaka — 1207</p>
                    <p class="sv-contact-line">📞 02-9876543</p>
                    <p class="sv-contact-line">✉️ info@saraswatividya.edu.bd</p>
                </div>
                <div>
                    <h3 class="sv-hours-title">Office Hours</h3>
                    <p class="sv-contact-line">Sat — Thu : 8:00 AM — 4:00 PM</p>
                    <p class="sv-contact-line">Friday : Closed</p>
                </div>
            </div>
        </section>

        <!-- ── Footer ─────────────────────────────────────────────── -->
        <footer class="sv-footer">
            <div class="sv-footer-inner">
                <div><strong>{{ siteSettings.institute_name }}</strong> · {{ siteSettings.tagline }}</div>
                <div>© 2026 · All rights reserved · Developed By <a href="https://crownsit.com" target="_blank" rel="noopener" style="color:var(--sv-brass);text-decoration:underline">Crowns IT</a></div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap');

/* ── Design tokens ────────────────────────────────────────────────────────── */
.sv-root {
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

    font-family: 'Inter', 'IBM Plex Mono', sans-serif;
    background: var(--sv-paper);
    color: var(--sv-ink);
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
    position: relative;
}

h1, h2, h3, .sv-stat-num {
    font-family: 'Fraunces', 'Inter', serif;
}
a { color: inherit; text-decoration: none; }

/* ── Paper texture ─────────────────────────────────────────────────────────── */
.sv-paper-texture {
    position: fixed;
    inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(20,33,61,0.035) 1px, transparent 0);
    background-size: 22px 22px;
    pointer-events: none;
    z-index: 0;
}

/* ── Header ────────────────────────────────────────────────────────────────── */
.sv-header {
    position: sticky;
    top: 0;
    z-index: 50;
    background: var(--sv-ink);
    color: #fff;
    border-bottom: 3px solid var(--sv-brass);
}
.sv-topbar {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    gap: 16px;
}
.sv-brand { display: flex; align-items: center; gap: 12px; }
.sv-crest {
    width: 44px; height: 44px; border-radius: 50%;
    background: radial-gradient(circle at 35% 30%, var(--sv-brass) 0%, var(--sv-brass-dark) 70%);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Fraunces', serif; font-weight: 700; color: var(--sv-ink); font-size: 18px;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(200,155,60,0.25);
}
.sv-school-name { font-size: 19px; font-weight: 600; letter-spacing: 0.2px; line-height: 1.15; }
.sv-school-tag { font-size: 11.5px; color: rgba(255,255,255,0.6); font-family: 'IBM Plex Mono', monospace; letter-spacing: 0.5px; margin-top: 2px; }
.sv-nav { display: flex; gap: 20px; align-items: center; }
.sv-nav-link { font-size: 14px; font-weight: 500; color: rgba(255,255,255,0.75); padding: 4px 0; position: relative; transition: color .2s; }
.sv-nav-link:hover { color: #fff; }
.sv-nav-link--active { color: var(--sv-brass); }
.sv-nav-link--active::after { content: ''; position: absolute; left: 0; right: 0; bottom: -6px; height: 2px; background: var(--sv-brass); }
.sv-nav-cta {
    padding: 8px 18px;
    background: var(--sv-brass);
    color: var(--sv-ink);
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    transition: background .2s;
}
.sv-nav-cta:hover { background: var(--sv-brass-dark); color: #fff; }
@media (max-width: 760px) { .sv-nav { display: none; } }

/* ── Hero Slider ────────────────────────────────────────────────────────────── */
.sv-hero {
    color: #fff;
    padding: 0;
    position: relative;
    overflow: hidden;
    z-index: 1;
    min-height: 500px;
    max-height: 500px;
}
.sv-slide {
    position: absolute;
    inset: 0;
    padding: 64px 24px 56px;
    display: flex;
    align-items: center;
    opacity: 0;
    z-index: 0;
    pointer-events: none;
    transform: scale(1.02);
    transition: opacity 0.8s ease-in-out, transform 0.8s ease-in-out;
}
.sv-slide--active {
    opacity: 1;
    z-index: 10;
    pointer-events: auto;
    transform: scale(1);
}
.sv-slider-controls-overlay {
    position: absolute;
    bottom: 56px;
    left: 24px;
    right: 24px;
    z-index: 20;
    pointer-events: auto;
}
.sv-hero-deco {
    position: absolute; right: -60px; top: -60px; width: 320px; height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(200,155,60,0.18) 0%, transparent 70%);
    pointer-events: none;
}
.sv-hero-img-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to right, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.35) 50%, rgba(0,0,0,0.15) 100%);
    z-index: 0;
}
.sv-hero-inner { max-width: 1200px; margin: 0 auto; position: relative; z-index: 1; }
.sv-eyebrow {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px; letter-spacing: 2px;
    color: var(--sv-brass); text-transform: uppercase; margin-bottom: 12px;
}
.sv-eyebrow--dark { color: var(--sv-brass-dark); }

.sv-slide-content { min-height: 160px; }
.sv-slide-content h1 { font-size: clamp(26px, 4vw, 42px); font-weight: 600; margin: 0 0 12px; max-width: 680px; line-height: 1.25; }
.sv-slide-subtitle { font-size: 17px; font-weight: 600; color: var(--sv-brass); margin: 0 0 8px; }
.sv-hero-sub { font-size: 15.5px; color: rgba(255,255,255,0.72); max-width: 560px; line-height: 1.7; margin: 0 0 20px; }
.sv-slide-btn {
    display: inline-block; margin-top: 6px;
    padding: 10px 24px; background: var(--sv-brass); color: var(--sv-ink);
    border-radius: 999px; font-size: 14px; font-weight: 700;
    transition: background .2s, transform .2s;
}
.sv-slide-btn:hover { background: var(--sv-brass-dark); color: #fff; transform: translateY(-2px); }

/* Slider controls */
.sv-slider-controls {
    display: flex; align-items: center; gap: 16px; margin-top: 32px;
}
.sv-slider-arrow {
    width: 36px; height: 36px; border-radius: 50%;
    background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
    color: #fff; font-size: 22px; font-weight: 300;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background .2s;
    line-height: 1;
}
.sv-slider-arrow:hover { background: rgba(255,255,255,0.28); }
.sv-slider-dots { display: flex; gap: 8px; }
.sv-dot {
    width: 10px; height: 10px; border-radius: 50%;
    background: rgba(255,255,255,0.35); border: none; cursor: pointer;
    transition: background .25s, transform .25s;
}
.sv-dot--active { background: var(--sv-brass); transform: scale(1.3); }

/* Slide transition */
.slide-fade-enter-active { transition: opacity .3s ease, transform .3s ease; }
.slide-fade-leave-active { transition: opacity .25s ease, transform .25s ease; }
.slide-fade-enter-from { opacity: 0; transform: translateY(12px); }
.slide-fade-leave-to   { opacity: 0; transform: translateY(-8px); }

/* ── Controls ──────────────────────────────────────────────────────────────── */
.sv-controls-wrap {
    max-width: 1200px;
    margin: -26px auto 0;
    padding: 0 24px;
    position: relative;
    z-index: 2;
}
.sv-controls {
    background: var(--sv-card);
    border: 1px solid var(--sv-line);
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: 0 14px 30px -18px rgba(20,33,61,0.35);
    display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between;
}
.sv-filters { display: flex; flex-wrap: wrap; gap: 8px; }
.sv-filter-btn {
    font-family: 'Inter', sans-serif;
    font-size: 13.5px; font-weight: 500;
    padding: 7px 16px; border-radius: 999px;
    border: 1px solid var(--sv-line);
    background: #fff; color: var(--sv-text-soft);
    cursor: pointer; transition: all .15s ease;
}
.sv-filter-btn:hover { border-color: var(--sv-brass); color: var(--sv-ink); }
.sv-filter-btn--active { background: var(--sv-ink); color: #fff; border-color: var(--sv-ink); }
.sv-search-box {
    display: flex; align-items: center; gap: 8px;
    border: 1px solid var(--sv-line); border-radius: 999px;
    padding: 8px 16px; background: #fff; min-width: 220px;
}
.sv-search-box input {
    border: none; outline: none;
    font-family: 'Inter', sans-serif; font-size: 13.5px;
    width: 100%; background: transparent; color: var(--sv-ink);
}
.sv-search-box svg { flex-shrink: 0; opacity: 0.5; }

/* ── Main / Notice Grid ────────────────────────────────────────────────────── */
.sv-main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 38px 24px 70px;
    position: relative;
    z-index: 1;
}
.sv-section-label { display: flex; align-items: baseline; gap: 10px; margin-bottom: 20px; }
.sv-section-label h2 { font-size: 20px; font-weight: 600; margin: 0; color: var(--sv-ink); }
.sv-section-label span { font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--sv-text-soft); }

.sv-notice-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px 20px;
}
@media (max-width: 920px) { .sv-notice-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .sv-notice-grid { grid-template-columns: 1fr; } }

/* Notice card with bulletin-board tilt */
.sv-notice-card {
    background: var(--sv-card);
    border: 1px solid var(--sv-line);
    border-radius: 3px 3px 10px 10px;
    padding: 26px 18px 18px;
    position: relative;
    box-shadow: 0 10px 22px -16px rgba(20,33,61,0.4);
    transition: transform .18s ease, box-shadow .18s ease;
}
.sv-notice-card[data-index="0"] { transform: rotate(-0.6deg); }
.sv-notice-card[data-index="1"] { transform: rotate(0.5deg); }
.sv-notice-card[data-index="2"] { transform: rotate(-0.3deg); }
.sv-notice-card:hover { transform: rotate(0deg) translateY(-5px); box-shadow: 0 22px 32px -18px rgba(20,33,61,0.45); }

/* Pin */
.sv-pin {
    position: absolute; top: -9px; left: 50%; transform: translateX(-50%);
    width: 16px; height: 16px; border-radius: 50%;
    background: radial-gradient(circle at 35% 30%, #e2b25c, var(--sv-brass-dark));
    box-shadow: 0 3px 6px rgba(0,0,0,0.3);
}
.sv-notice-card--urgent .sv-pin {
    background: radial-gradient(circle at 35% 30%, #d96b6b, var(--sv-urgent));
}

/* Tape label */
.sv-tape {
    position: absolute; top: 14px; right: -6px;
    font-family: 'IBM Plex Mono', monospace; font-size: 10px; font-weight: 500;
    letter-spacing: 0.5px; padding: 4px 10px 4px 12px;
    color: #fff; border-radius: 2px 0 0 2px;
    box-shadow: -2px 2px 5px rgba(0,0,0,0.15);
    text-transform: capitalize;
}

.sv-notice-meta {
    display: flex; align-items: center; gap: 8px; margin-bottom: 10px;
    font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--sv-text-soft);
}
.sv-notice-card h3 { font-size: 16px; font-weight: 600; line-height: 1.4; margin: 0 0 8px; color: var(--sv-ink); }
.sv-notice-excerpt { font-size: 13px; line-height: 1.7; color: var(--sv-text-soft); margin: 0 0 14px; }
.sv-notice-footer {
    display: flex; align-items: center; justify-content: space-between;
    border-top: 1px dashed var(--sv-line); padding-top: 10px;
}
.sv-notice-by { font-size: 11px; color: var(--sv-text-soft); }
.sv-attach-badge { font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; color: var(--sv-text-soft); display: flex; align-items: center; gap: 4px; }

/* Empty state */
.sv-empty-state { grid-column: 1/-1; text-align: center; padding: 64px 20px; color: var(--sv-text-soft); font-size: 15px; }
.sv-empty-emoji { font-size: 32px; margin-bottom: 12px; }

/* ── Portals ──────────────────────────────────────────────────────────────── */
.sv-portal-tabs { display: flex; gap: 10px; margin-bottom: 24px; }
.sv-ptab {
    padding: 10px 22px; border-radius: 999px;
    border: 1px solid var(--sv-line);
    background: #fff; color: var(--sv-text-soft);
    font-size: 14px; font-weight: 500; cursor: pointer; transition: all .15s;
}
.sv-ptab:hover { border-color: var(--sv-forest); color: var(--sv-forest); }
.sv-ptab--active { background: var(--sv-forest); color: #fff; border-color: var(--sv-forest); }

.sv-portal-grid { display: grid; grid-template-columns: 320px 1fr; gap: 24px; }
@media (max-width: 820px) { .sv-portal-grid { grid-template-columns: 1fr; } }

.sv-portal-panel {
    background: var(--sv-card);
    border: 1px solid var(--sv-line);
    border-radius: 14px; padding: 24px;
    box-shadow: 0 8px 20px -14px rgba(20,33,61,0.3);
    height: fit-content;
}
.sv-portal-panel-title { font-size: 17px; font-weight: 600; margin: 0 0 18px; color: var(--sv-ink); }
.sv-portal-form { display: flex; flex-direction: column; gap: 14px; }
.sv-form-label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px; color: var(--sv-ink); }
.sv-form-input {
    width: 100%; border: 1px solid var(--sv-line);
    border-radius: 8px; padding: 10px 14px;
    font-family: 'Inter', sans-serif; font-size: 14px;
    background: #fff; color: var(--sv-ink);
    outline: none; transition: border-color .2s;
    box-sizing: border-box;
}
.sv-form-input:focus { border-color: var(--sv-brass); }
.sv-form-btn {
    width: 100%; padding: 11px;
    background: var(--sv-forest); color: #fff;
    border: none; border-radius: 8px;
    font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 600;
    cursor: pointer; transition: background .2s;
}
.sv-form-btn:hover:not(:disabled) { background: var(--sv-forest-dark); }
.sv-form-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.sv-form-error { margin-top: 12px; padding: 12px; background: #fdf2f2; border: 1px solid #fca5a5; color: #b91c1c; font-size: 13px; border-radius: 8px; }

.sv-portal-placeholder {
    height: 200px; border: 1px dashed var(--sv-line);
    border-radius: 14px; display: flex; align-items: center; justify-content: center;
    color: var(--sv-text-soft); font-size: 14px; text-align: center; padding: 24px;
}
.sv-result-card {
    background: var(--sv-card);
    border: 1px solid var(--sv-line);
    border-radius: 14px; padding: 24px;
    box-shadow: 0 8px 20px -14px rgba(20,33,61,0.3);
}
.sv-result-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
.sv-result-card-header h4 { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--sv-text-soft); margin: 0; }
.sv-print-btn {
    padding: 7px 14px; background: var(--sv-ink); color: #fff;
    border: none; border-radius: 6px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: background .2s;
}
.sv-print-btn:hover { background: var(--sv-forest); }

.sv-marksheet { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid var(--sv-line); }
.sv-marksheet-header { text-align: center; border-bottom: 2px solid var(--sv-ink); padding-bottom: 12px; margin-bottom: 16px; }
.sv-marksheet-header h2 { font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 4px; color: var(--sv-ink); }
.sv-marksheet-header p { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: var(--sv-text-soft); margin: 0; }
.sv-marksheet-bio { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 13px; margin-bottom: 16px; }
.sv-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sv-table th { background: #f0f0ec; border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; padding: 8px; font-weight: 700; text-align: left; }
.sv-table td { padding: 8px; border-bottom: 1px solid var(--sv-line); }
.sv-marksheet-summary { border-top: 1px solid #ccc; margin-top: 14px; padding-top: 12px; display: flex; justify-content: space-between; font-size: 13px; }
.sv-pass-badge { padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
.sv-pass-badge--pass { background: #dcfce7; color: #15803d; }
.sv-pass-badge--fail { background: #fee2e2; color: #b91c1c; }

/* ── About ─────────────────────────────────────────────────────────────────── */
.sv-about {
    background: var(--sv-card);
    padding: 72px 24px;
    border-top: 1px solid var(--sv-line);
    border-bottom: 1px solid var(--sv-line);
    position: relative; z-index: 1;
}
.sv-about-inner {
    max-width: 1200px; margin: 0 auto;
    display: grid; grid-template-columns: 1.1fr 1fr; gap: 56px; align-items: center;
}
@media (max-width: 860px) { .sv-about-inner { grid-template-columns: 1fr; } }
.sv-about-text h2 { font-size: clamp(22px, 3vw, 30px); font-weight: 600; line-height: 1.35; margin: 0 0 14px; color: var(--sv-ink); }
.sv-about-text p { font-size: 14.5px; line-height: 1.85; color: var(--sv-text-soft); max-width: 480px; margin: 0 0 18px; }
.sv-text-link { font-size: 13.5px; font-weight: 600; color: var(--sv-forest); border-bottom: 1px solid var(--sv-forest); padding-bottom: 2px; }
.sv-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.sv-stat-box { background: var(--sv-ink); color: #fff; border-radius: 12px; padding: 22px 18px; }
.sv-stat-num { font-family: 'Fraunces', serif; font-size: 28px; font-weight: 600; color: var(--sv-brass); }
.sv-stat-label { font-size: 12.5px; color: rgba(255,255,255,0.65); margin-top: 4px; }

/* ── Programs ──────────────────────────────────────────────────────────────── */
.sv-programs { padding: 72px 24px; position: relative; z-index: 1; }
.sv-programs-inner { max-width: 1200px; margin: 0 auto; }
.sv-program-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
@media (max-width: 860px) { .sv-program-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .sv-program-grid { grid-template-columns: 1fr; } }
.sv-program-card {
    background: var(--sv-card); border: 1px solid var(--sv-line); border-radius: 12px;
    padding: 22px 18px; transition: border-color .2s, transform .2s;
}
.sv-program-card:hover { border-color: var(--sv-brass); transform: translateY(-3px); }
.sv-program-tag {
    display: inline-block;
    font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; letter-spacing: 0.5px;
    color: var(--sv-forest); background: rgba(31,77,58,0.08);
    padding: 4px 10px; border-radius: 999px; margin-bottom: 12px;
}
.sv-program-card h3 { font-size: 16px; font-weight: 600; margin: 0 0 8px; color: var(--sv-ink); }
.sv-program-card p { font-size: 13px; line-height: 1.65; color: var(--sv-text-soft); margin: 0; }
.sv-program-card--expanded { border-color: var(--sv-brass); background: rgba(31,77,58,0.04); }
.sv-subject-list {
    margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--sv-line);
    animation: svSubjectSlide .25s ease-out;
}
@keyframes svSubjectSlide { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
.sv-subject-list-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    color: var(--sv-forest); margin-bottom: 8px;
}
.sv-subject-list ul { list-style: none; margin: 0; padding: 0; }
.sv-subject-list li {
    display: flex; align-items: center; gap: 8px;
    padding: 5px 0; font-size: 13px; color: var(--sv-ink);
    border-bottom: 1px dashed rgba(0,0,0,.06);
}
.sv-subject-list li:last-child { border-bottom: none; }
.sv-subject-code {
    font-family: 'IBM Plex Mono', monospace; font-size: 10.5px;
    background: rgba(31,77,58,0.08); color: var(--sv-forest);
    padding: 2px 8px; border-radius: 4px; white-space: nowrap;
}

/* ── Gallery ──────────────────────────────────────────────────────────────── */
.sv-gallery { padding: 0 24px 72px; position: relative; z-index: 1; }
.sv-gallery-grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    gap: 14px; height: 380px;
}
@media (max-width: 760px) { .sv-gallery-grid { grid-template-columns: 1fr 1fr; grid-template-rows: repeat(4, 160px); height: auto; } }
.sv-gtile {
    border-radius: 12px; position: relative; overflow: hidden;
    display: flex; align-items: flex-end; padding: 16px;
    color: #fff; font-size: 13px; font-weight: 600;
    cursor: default;
}
.sv-gtile span { position: relative; z-index: 1; text-shadow: 0 2px 6px rgba(0,0,0,0.5); }
.sv-gtile::before { content: ''; position: absolute; inset: 0; opacity: 0.9; transition: opacity .3s; }
.sv-gtile:hover::before { opacity: 1; }
/* Tiles with a real image: light black overlay, no gradient/hover */
.sv-gtile.sv-has-image::before { background: rgba(0,0,0,0.28); opacity: 1; }
.sv-gtile.sv-has-image:hover::before { opacity: 1; }
.sv-g1 { grid-row: 1/3; }
.sv-g1::before { background: linear-gradient(160deg, #2c5f47, #173A2C); }
.sv-g2::before { background: linear-gradient(160deg, #3B5FA0, #1F3A63); }
.sv-g3::before { background: linear-gradient(160deg, #A87F2B, #7A5A1D); }
.sv-g4::before { background: linear-gradient(160deg, #7A4FA0, #4E3168); }

/* ── Contact ───────────────────────────────────────────────────────────────── */
.sv-contact { background: var(--sv-ink); color: #fff; padding: 64px 24px; position: relative; z-index: 1; }
.sv-contact .sv-eyebrow { color: var(--sv-brass); }
.sv-contact-inner {
    max-width: 1200px; margin: 0 auto;
    display: grid; grid-template-columns: 1fr 1fr; gap: 48px;
}
@media (max-width: 700px) { .sv-contact-inner { grid-template-columns: 1fr; } }
.sv-contact-inner h2 { font-size: 24px; font-weight: 600; margin: 10px 0 18px; }
.sv-hours-title { font-size: 16px; font-weight: 600; margin: 0 0 14px; color: var(--sv-brass); }
.sv-contact-line { font-size: 14px; line-height: 2.1; color: rgba(255,255,255,0.75); margin: 0; }

/* ── Footer ───────────────────────────────────────────────────────────────── */
.sv-footer { background: black; color: rgba(255,255,255,0.7); padding: 28px 24px; position: relative; z-index: 1; }
.sv-footer-inner {
    max-width: 1200px; margin: 0 auto;
    display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; font-size: 12.5px;
}
.sv-footer-inner strong { color: #fff; }

@media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
</style>
