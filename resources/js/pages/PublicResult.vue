<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const siteSettings = computed(() => page.props.site_settings as {
    institute_name: string;
    tagline: string;
    logo_path: string | null;
    favicon_path: string | null;
});

interface Student {
    student_id: string;
    full_name_en: string;
    class: string;
    section: string;
    roll_number: number;
    program_name: string;
}

interface ExamResult {
    exam_name: string;
    marks: Record<string, number>;
    gpa: string;
    grade: string;
    pass_status: 'pass' | 'fail';
    remarks: string | null;
}

interface ResultHistoryItem {
    id: number;
    exam_name: string;
    semester_name: string;
    semester_id: number;
    marks: Record<string, number>;
    total_marks: number;
    percentage: number;
    gpa: string;
    grade: string;
    pass_status: 'pass' | 'fail';
    is_final: boolean;
    date: string;
}

interface SemesterExam {
    id: number;
    name: string;
    is_final: boolean;
    sort_order: number;
}

interface Semester {
    id: number;
    name: string;
    sort_order: number;
    exams: SemesterExam[];
}

const props = defineProps<{
    semesters: Semester[];
    sections: string[];
    resultData: { student: Student; result: ExamResult } | null;
    transcriptData: { student: Student; groupedHistory: Record<string, ResultHistoryItem[]> } | null;
    resultError: string | null;
    filters: {
        student_id?: string;
        semester_id?: string;
        semester_exam_id?: string;
        section?: string;
        lookup_type?: 'single' | 'transcript';
    };
}>();

const selectedSemesterId = ref<string>(props.filters.semester_id || '');
const mobileMenuOpen = ref(false);
const filteredExams = computed(() => {
    const sem = props.semesters.find(s => s.id === Number(selectedSemesterId.value));
    return sem ? sem.exams : [];
});

const form = useForm({
    student_id: props.filters.student_id || '',
    semester_id: props.filters.semester_id || '',
    semester_exam_id: props.filters.semester_exam_id || '',
    section: props.filters.section || '',
    lookup_type: props.filters.lookup_type || 'single',
});

function onSemesterChange() {
    selectedSemesterId.value = form.semester_id;
    form.semester_exam_id = '';
}

// Compute CGPA and final grade across semesters for the transcript
const cgpa = computed(() => {
    if (!props.transcriptData) return '0.00';
    const semesters = Object.keys(props.transcriptData.groupedHistory);
    const semesterCount = semesters.length;

    if (semesterCount >= 8) {
        let totalFinalGpa = 0;
        let finalExamCount = 0;

        Object.values(props.transcriptData.groupedHistory).forEach(exams => {
            const finalExam = exams.find(e => e.is_final);
            if (finalExam) {
                const val = parseFloat(finalExam.gpa);
                if (!isNaN(val)) {
                    totalFinalGpa += val;
                    finalExamCount++;
                }
            } else {
                if (exams.length > 0) {
                    const lastExam = exams[exams.length - 1];
                    const val = parseFloat(lastExam.gpa);
                    if (!isNaN(val)) {
                        totalFinalGpa += val;
                        finalExamCount++;
                    }
                }
            }
        });

        return finalExamCount > 0 ? (totalFinalGpa / finalExamCount).toFixed(2) : '0.00';
    } else {
        const allExams = Object.values(props.transcriptData.groupedHistory).flat();
        if (allExams.length === 0) return '0.00';

        const sortedExams = [...allExams].sort((a, b) => a.id - b.id);
        const lastExam = sortedExams[sortedExams.length - 1];

        return parseFloat(lastExam.gpa).toFixed(2);
    }
});

const cgpaGrade = computed(() => {
    const score = parseFloat(cgpa.value);
    if (isNaN(score) || score <= 0) return 'N/A';
    if (score >= 4.0) return 'A+';
    if (score >= 3.5) return 'A';
    if (score >= 3.0) return 'B';
    if (score >= 2.5) return 'C';
    if (score >= 2.0) return 'D';
    return 'F';
});

function submit() {
    form.get('/result', { preserveScroll: true });
}

function getSubjectGp(score: number): number {
    if (score >= 80) return 4.0;
    if (score >= 70) return 3.5;
    if (score >= 60) return 3.0;
    if (score >= 50) return 2.5;
    if (score >= 40) return 2.0;
    return 0.0;
}

function getSubjectGrade(score: number): string {
    if (score >= 80) return 'A+';
    if (score >= 70) return 'A';
    if (score >= 60) return 'B';
    if (score >= 50) return 'C';
    if (score >= 40) return 'D';
    return 'F';
}

function printMarksheet() {
    const printContent = document.getElementById('printable-marksheet')?.innerHTML;
    const original = document.body.innerHTML;
    if (printContent) {
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = original;
        window.location.reload();
    }
}
</script>

<template>
    <Head :title="'Student Result — ' + siteSettings.institute_name" />

    <div class="pr-root">
        <!-- Paper texture -->
        <div class="pr-texture" aria-hidden="true"></div>

        <!-- Header -->
        <header class="pr-header">
            <div class="pr-topbar">
                <Link href="/" class="pr-brand">
                    <img v-if="siteSettings.logo_path" :src="'/storage/' + siteSettings.logo_path" class="w-10 h-10 object-contain rounded-md mr-2" alt="Logo" />
                    <div v-else class="pr-crest">SV</div>
                    <div class="pr-brand-text">
                        <div class="pr-school-name">{{ siteSettings.institute_name }}</div>
                        <div class="pr-school-tag">{{ siteSettings.tagline }}</div>
                    </div>
                </Link>
                <nav class="pr-nav">
                    <Link href="/" class="pr-nav-link">Home</Link>
                    <Link href="/#notices" class="pr-nav-link">Notice Board</Link>
                    <Link href="/#about" class="pr-nav-link">About Us</Link>
                    <Link href="/#programs" class="pr-nav-link">Programs</Link>
                    <Link href="/result" class="pr-nav-link pr-nav-link--active">Results</Link>
                    <Link href="/#contact" class="pr-nav-link">Contact</Link>
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="pr-nav-cta"
                    >Dashboard</Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="pr-nav-cta"
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
                    <div class="pr-brand">
                        <img v-if="siteSettings.logo_path" :src="'/storage/' + siteSettings.logo_path" class="w-8 h-8 object-contain rounded-md" alt="Logo" />
                        <div v-else class="pr-crest">SV</div>
                        <div class="pr-brand-text">
                            <div class="pr-school-name" style="font-size: 16px;">{{ siteSettings.institute_name }}</div>
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

        <!-- Hero -->
        <section class="pr-hero">
            <div class="pr-hero-inner">
                <div class="pr-eyebrow">Student Portal · Exam Results</div>
                <h1>Check Your Exam Result</h1>
                <p class="pr-hero-sub">Enter your Student ID and select the exam to view your detailed mark sheet with grades and GPA.</p>
            </div>
        </section>

        <!-- Main Content -->
        <main class="pr-main">
            <div class="pr-layout">

                <!-- Search Panel -->
                <div class="pr-search-panel">
                    <div class="pr-panel-header">
                        <div class="pr-panel-icon">📋</div>
                        <h2>Result Lookup</h2>
                    </div>

                    <!-- Search Type Tabs -->
                    <div class="pr-tabs" style="display:flex;margin-bottom:18px;border-bottom:2px solid var(--line, #e5e5e5)">
                        <button 
                            type="button" 
                            @click="form.lookup_type = 'single'"
                            :class="['pr-tab-btn', { 'pr-tab-btn--active': form.lookup_type === 'single' }]"
                            style="flex:1;padding:10px 12px;font-size:13px;font-weight:700;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;text-align:center"
                        >
                            Single Exam
                        </button>
                        <button 
                            type="button" 
                            @click="form.lookup_type = 'transcript'"
                            :class="['pr-tab-btn', { 'pr-tab-btn--active': form.lookup_type === 'transcript' }]"
                            style="flex:1;padding:10px 12px;font-size:13px;font-weight:700;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;text-align:center"
                        >
                            All Semesters
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="pr-form">
                        <div class="pr-field">
                            <label class="pr-label">Student ID</label>
                            <input
                                v-model="form.student_id"
                                type="text"
                                placeholder="e.g. STU-2026-0001"
                                class="pr-input"
                                autocomplete="off"
                            />
                            <span class="pr-hint">Your student ID card number</span>
                        </div>

                        <div v-if="form.lookup_type === 'single'" class="pr-field">
                            <label class="pr-label">Select Semester</label>
                            <select v-model="form.semester_id" @change="onSemesterChange" class="pr-input pr-select" required>
                                <option value="">-- Choose Semester --</option>
                                <option v-for="sem in semesters" :key="sem.id" :value="sem.id">
                                    {{ sem.name }}
                                </option>
                            </select>
                        </div>

                        <div v-if="form.lookup_type === 'single'" class="pr-field">
                            <label class="pr-label">Select Exam</label>
                            <select v-model="form.semester_exam_id" class="pr-input pr-select" :disabled="!form.semester_id" required>
                                <option value="">-- Choose Exam --</option>
                                <option v-for="exam in filteredExams" :key="exam.id" :value="exam.id">
                                    {{ exam.name }}
                                </option>
                            </select>
                        </div>

                        <div v-if="form.lookup_type === 'single'" class="pr-field">
                            <label class="pr-label">Select Section (Optional)</label>
                            <select v-model="form.section" class="pr-input pr-select">
                                <option value="">All Sections</option>
                                <option v-for="sec in sections" :key="sec" :value="sec">
                                    Section {{ sec }}
                                </option>
                            </select>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing || !form.student_id.trim() || (form.lookup_type === 'single' && !form.semester_exam_id)"
                            class="pr-submit-btn"
                        >
                            <span v-if="form.processing">Searching...</span>
                            <span v-else>🔍 Search Result</span>
                        </button>
                    </form>

                    <!-- Error -->
                    <div v-if="resultError" class="pr-error">
                        <span class="pr-error-icon">⚠️</span>
                        {{ resultError }}
                    </div>

                    <!-- Info box -->
                    <div class="pr-info-box">
                        <p class="pr-info-title">📌 How to find your ID?</p>
                        <p>Your Student ID is printed on your admit card and identity card. Format: <strong>STU-YEAR-XXXX</strong></p>
                    </div>
                </div>

                <!-- Result Display -->
                <div class="pr-result-area">
                    <!-- Empty state -->
                    <div v-if="!resultData && !transcriptData && !resultError" class="pr-empty">
                        <div class="pr-empty-icon">🎓</div>
                        <h3>No result loaded yet</h3>
                        <p>Enter your Student ID and select the query mode on the left to view your result details.</p>
                    </div>

                    <!-- Complete Academic Transcript -->
                    <div v-if="transcriptData" class="pr-marksheet-wrap">
                        <div class="pr-marksheet-actions">
                            <button @click="printMarksheet" class="pr-print-btn">🖨 Print Transcript</button>
                        </div>

                        <div id="printable-marksheet" class="pr-marksheet animate-in fade-in duration-200">
                            <!-- School Header -->
                            <div class="pr-ms-header" style="background:#14213d;color:#fff;padding:24px 28px;display:flex;align-items:center;gap:18px">
                                <img v-if="siteSettings.logo_path" :src="'/storage/' + siteSettings.logo_path" class="w-12 h-12 object-contain rounded-md mr-3" alt="Logo" />
                                <div v-else class="pr-ms-crest">SV</div>
                                <div>
                                    <h2 style="font-size:20px;font-weight:700;margin:0 0 4px">{{ siteSettings.institute_name }}</h2>
                                    <p style="font-size:11px;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:1.5px;margin:0">Official Academic Transcript</p>
                                </div>
                            </div>

                            <!-- CGPA Summary Banner -->
                            <div class="pr-ms-status-bar" style="background:#eef2ff;color:#4f46e5;border-bottom:1px solid rgba(79,70,229,0.1);display:flex;align-items:center;justify-content:between;padding:12px 28px;font-size:14px;font-weight:600">
                                <span>Academic Status: ACTIVE</span>
                                <span class="pr-ms-status-badge">CGPA: {{ cgpa }} (Grade: {{ cgpaGrade }})</span>
                            </div>

                            <!-- Student Info -->
                            <div class="pr-ms-bio" style="padding:20px 28px;border-bottom:1px solid var(--line)">
                                <div class="pr-ms-bio-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                                    <div class="pr-ms-bio-item">
                                        <span class="pr-ms-bio-label" style="font-size:10.5px;text-transform:uppercase;letter-spacing:0.5px;color:var(--soft);font-weight:600">Student Name</span>
                                        <span class="pr-ms-bio-value" style="font-size:15px;font-weight:700;color:var(--ink)">{{ transcriptData.student.full_name_en }}</span>
                                    </div>
                                    <div class="pr-ms-bio-item">
                                        <span class="pr-ms-bio-label" style="font-size:10.5px;text-transform:uppercase;letter-spacing:0.5px;color:var(--soft);font-weight:600">Student ID</span>
                                        <span class="pr-ms-bio-value pr-mono" style="font-size:15px;font-weight:700;color:var(--ink)">{{ transcriptData.student.student_id }}</span>
                                    </div>
                                    <div class="pr-ms-bio-item">
                                        <span class="pr-ms-bio-label" style="font-size:10.5px;text-transform:uppercase;letter-spacing:0.5px;color:var(--soft);font-weight:600">Program & Section</span>
                                        <span class="pr-ms-bio-value" style="font-size:15px;font-weight:700;color:var(--ink)">{{ transcriptData.student.program_name }} — {{ transcriptData.student.section }}</span>
                                    </div>
                                    <div class="pr-ms-bio-item">
                                        <span class="pr-ms-bio-label" style="font-size:10.5px;text-transform:uppercase;letter-spacing:0.5px;color:var(--soft);font-weight:600">Roll Number</span>
                                        <span class="pr-ms-bio-value" style="font-size:15px;font-weight:700;color:var(--ink)">{{ transcriptData.student.roll_number }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Semester Wise Breakdown -->
                            <div class="pr-transcript-semesters" style="padding: 20px 28px; display:flex; flex-direction:column; gap: 24px;">
                                <div v-for="(exams, semesterName) in transcriptData.groupedHistory" :key="semesterName" class="pr-transcript-sem" style="border:1.5px solid var(--line);border-radius:12px;overflow:hidden">
                                    <div style="background:#f8fafc;padding:12px 16px;font-weight:800;font-size:13.5px;color:var(--ink);border-bottom:1.5px solid var(--line);display:flex;justify-content:space-between;align-items:center">
                                        <span>{{ semesterName }}</span>
                                    </div>

                                    <div style="display:flex;flex-direction:column;gap:16px;padding:16px">
                                        <div v-for="exam in exams" :key="exam.id" style="border:1px dashed var(--line);border-radius:8px;padding:12px">
                                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                                                <span style="font-weight:700;font-size:13px;color:var(--ink)">{{ exam.exam_name }}</span>
                                                <div style="display:flex;gap:8px;align-items:center">
                                                    <span :class="exam.pass_status === 'pass' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'" style="font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid">
                                                        {{ exam.pass_status === 'pass' ? 'PASSED' : 'FAILED' }}
                                                    </span>
                                                    <span style="font-size:11px;font-weight:800;color:var(--ink);font-family:'IBM Plex Mono',monospace">
                                                        GPA: {{ exam.gpa }} ({{ exam.grade }})
                                                    </span>
                                                </div>
                                            </div>

                                            <table style="width:100%;font-size:11.5px;border-collapse:collapse">
                                                <thead>
                                                    <tr style="border-bottom:1px solid var(--line);color:var(--soft);text-align:left;font-weight:700">
                                                        <th style="padding:4px 0">Subject</th>
                                                        <th style="padding:4px 0;text-align:center">Score</th>
                                                        <th style="padding:4px 0;text-align:center">Grade Point</th>
                                                        <th style="padding:4px 0;text-align:center">Grade</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(score, subject) in exam.marks" :key="subject" style="border-bottom:1px dashed rgba(0,0,0,0.04)" :class="{ 'bg-red-50/20': score < 40 }">
                                                        <td style="padding:4px 0;font-weight:600">{{ subject }}</td>
                                                        <td style="padding:4px 0;text-align:center;font-weight:700">{{ score }}</td>
                                                        <td style="padding:4px 0;text-align:center;font-family:'IBM Plex Mono',monospace">{{ getSubjectGp(score).toFixed(2) }}</td>
                                                        <td style="padding:4px 0;text-align:center;font-weight:800" :class="score >= 40 ? 'text-green-600' : 'text-red-500'">{{ getSubjectGrade(score) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pr-ms-official">
                                Transmitted from official database records · {{ new Date().toLocaleString() }}
                            </div>
                        </div>
                    </div>

                    <!-- Mark Sheet -->
                    <div v-if="resultData" class="pr-marksheet-wrap">
                        <!-- Print button -->
                        <div class="pr-marksheet-actions">
                            <button @click="printMarksheet" class="pr-print-btn">🖨 Print Mark Sheet</button>
                        </div>

                        <div id="printable-marksheet" class="pr-marksheet">
                            <!-- School Header -->
                            <div class="pr-ms-header">
                                <img v-if="siteSettings.logo_path" :src="'/storage/' + siteSettings.logo_path" class="w-12 h-12 object-contain rounded-md mr-3" alt="Logo" />
                                <div v-else class="pr-ms-crest">SV</div>
                                <div>
                                    <h2>{{ siteSettings.institute_name }}</h2>
                                    <p>Student Academic Mark Sheet — Official Copy</p>
                                </div>
                            </div>

                            <!-- Status Banner -->
                            <div
                                class="pr-ms-status-bar"
                                :class="resultData.result.pass_status === 'pass' ? 'pr-ms-status-bar--pass' : 'pr-ms-status-bar--fail'"
                            >
                                <span>{{ resultData.result.exam_name }}</span>
                                <span class="pr-ms-status-badge">
                                    {{ resultData.result.pass_status === 'pass' ? '✅ PASSED' : '❌ FAILED' }}
                                </span>
                            </div>

                            <!-- Student Info -->
                            <div class="pr-ms-bio">
                                <div class="pr-ms-bio-grid">
                                    <div class="pr-ms-bio-item">
                                        <span class="pr-ms-bio-label">Student Name</span>
                                        <span class="pr-ms-bio-value">{{ resultData.student.full_name_en }}</span>
                                    </div>
                                    <div class="pr-ms-bio-item">
                                        <span class="pr-ms-bio-label">Student ID</span>
                                        <span class="pr-ms-bio-value pr-mono">{{ resultData.student.student_id }}</span>
                                    </div>
                                    <div class="pr-ms-bio-item">
                                        <span class="pr-ms-bio-label">Program</span>
                                        <span class="pr-ms-bio-value">{{ resultData.student.program_name }} — {{ resultData.student.section }}</span>
                                    </div>
                                    <div class="pr-ms-bio-item">
                                        <span class="pr-ms-bio-label">Roll Number</span>
                                        <span class="pr-ms-bio-value">{{ resultData.student.roll_number }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Subject Marks Table -->
                            <table class="pr-ms-table">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Score</th>
                                        <th>Grade Point</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(score, subject) in resultData.result.marks"
                                        :key="subject"
                                        :class="{ 'pr-ms-table-fail': score < 40 }"
                                    >
                                        <td class="pr-ms-subject">{{ subject }}</td>
                                        <td class="pr-ms-center pr-ms-score">{{ score }}</td>
                                        <td class="pr-ms-center pr-mono">{{ getSubjectGp(score).toFixed(2) }}</td>
                                        <td class="pr-ms-center pr-ms-grade" :class="{ 'pr-ms-grade--fail': score < 40 }">
                                            {{ getSubjectGrade(score) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Summary Footer -->
                            <div class="pr-ms-summary">
                                <div class="pr-ms-remarks">
                                    <span class="pr-ms-remarks-label">Remarks</span>
                                    <em>{{ resultData.result.remarks || 'Satisfactory performance.' }}</em>
                                </div>
                                <div class="pr-ms-gpa-box">
                                    <div class="pr-ms-gpa">
                                        <span class="pr-ms-gpa-label">GPA</span>
                                        <span class="pr-ms-gpa-value">{{ resultData.result.gpa }}</span>
                                    </div>
                                    <div class="pr-ms-grade-final">
                                        <span class="pr-ms-gpa-label">Final Grade</span>
                                        <span class="pr-ms-grade-final-value">{{ resultData.result.grade }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Official note -->
                            <div class="pr-ms-official">
                                This is a computer-generated mark sheet. {{ siteSettings.institute_name }} · {{ siteSettings.tagline }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="pr-footer">
            <div class="pr-footer-inner">
                <div><strong>{{ siteSettings.institute_name }}</strong> · {{ siteSettings.tagline }}</div>
                <div>© 2026 · All rights reserved · Developed By <a href="https://crownsit.com" target="_blank" rel="noopener" style="color:#b8860b;text-decoration:underline">Crowns IT</a></div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap');

/* Tokens */
.pr-root {
    --ink: #14213D;
    --paper: #EEF1E9;
    --card: #FBFAF5;
    --forest: #1F4D3A;
    --forest-dark: #173A2C;
    --brass: #C89B3C;
    --brass-dark: #A87F2B;
    --urgent: #B33A3A;
    --line: rgba(20,33,61,0.12);
    --soft: rgba(20,33,61,0.62);

    font-family: 'Inter', sans-serif;
    background: var(--paper);
    color: var(--ink);
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
    display: flex; flex-direction: column;
}

h1, h2, h3 { font-family: 'Fraunces', 'Inter', serif; }
a { color: inherit; text-decoration: none; }

.pr-texture {
    position: fixed; inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(20,33,61,0.035) 1px, transparent 0);
    background-size: 22px 22px;
    pointer-events: none; z-index: 0;
}

/* Header */
.pr-header {
    position: sticky; top: 0; z-index: 50;
    background: var(--ink); color: #fff;
    border-bottom: 3px solid var(--brass);
}
.pr-topbar {
    max-width: 1200px; margin: 0 auto;
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 24px; gap: 16px;
}
.pr-brand { display: flex; align-items: center; gap: 12px; }
.pr-crest {
    width: 42px; height: 42px; border-radius: 50%;
    background: radial-gradient(circle at 35% 30%, var(--brass) 0%, var(--brass-dark) 70%);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Fraunces', serif; font-weight: 700; color: var(--ink); font-size: 17px;
    box-shadow: 0 0 0 3px rgba(200,155,60,0.25); flex-shrink: 0;
}
.pr-school-name { font-size: 18px; font-weight: 600; line-height: 1.2; }
.pr-school-tag { font-size: 11px; color: rgba(255,255,255,0.55); font-family: 'IBM Plex Mono', monospace; letter-spacing: 0.5px; }
.pr-nav { display: flex; align-items: center; gap: 20px; }
.pr-nav-link { font-size: 14px; font-weight: 500; color: rgba(255,255,255,0.72); padding: 4px 0; position: relative; transition: color .2s; }
.pr-nav-link:hover { color: #fff; }
.pr-nav-link--active { color: var(--brass); }
.pr-nav-link--active::after { content: ''; position: absolute; left: 0; right: 0; bottom: -6px; height: 2px; background: var(--brass); }
.pr-nav-cta {
    padding: 8px 18px; background: var(--brass); color: var(--ink);
    border-radius: 999px; font-size: 13px; font-weight: 700; transition: background .2s;
}
.pr-nav-cta:hover { background: var(--brass-dark); color: #fff; }
@media (max-width: 720px) { .pr-nav { display: none; } }

/* Hero */
.pr-hero {
    background: linear-gradient(180deg, var(--forest) 0%, var(--forest-dark) 100%);
    color: #fff; padding: 52px 24px 44px;
    position: relative; overflow: hidden; z-index: 1;
}
.pr-hero::after {
    content: ''; position: absolute; right: -60px; top: -60px;
    width: 260px; height: 260px; border-radius: 50%;
    background: radial-gradient(circle, rgba(200,155,60,0.18) 0%, transparent 70%);
}
.pr-hero-inner { max-width: 1200px; margin: 0 auto; position: relative; z-index: 1; }
.pr-eyebrow {
    font-family: 'IBM Plex Mono', monospace; font-size: 12px; letter-spacing: 2px;
    color: var(--brass); text-transform: uppercase; margin-bottom: 10px;
}
.pr-hero-inner h1 { font-size: clamp(26px, 4vw, 40px); font-weight: 600; margin: 0 0 12px; line-height: 1.25; }
.pr-hero-sub { font-size: 15px; color: rgba(255,255,255,0.7); max-width: 520px; line-height: 1.7; margin: 0; }

/* Main */
.pr-main { flex: 1; max-width: 1200px; margin: 0 auto; width: 100%; padding: 40px 24px 64px; position: relative; z-index: 1; }
.pr-layout { display: grid; grid-template-columns: 360px 1fr; gap: 32px; align-items: start; }
@media (max-width: 900px) { .pr-layout { grid-template-columns: 1fr; } }

/* Search Panel */
.pr-search-panel {
    background: var(--card); border: 1px solid var(--line);
    border-radius: 16px; padding: 28px;
    box-shadow: 0 12px 28px -16px rgba(20,33,61,0.32);
    position: sticky; top: 80px;
}
.pr-tab-btn {
    color: var(--soft);
    transition: color 0.2s, border-color 0.2s;
}
.pr-tab-btn:hover {
    color: var(--ink);
}
.pr-tab-btn--active {
    color: var(--forest) !important;
    border-bottom: 2px solid var(--forest) !important;
}
.pr-panel-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
.pr-panel-icon { font-size: 26px; }
.pr-panel-header h2 { font-size: 20px; font-weight: 600; margin: 0; color: var(--ink); }

.pr-form { display: flex; flex-direction: column; gap: 18px; }
.pr-field { display: flex; flex-direction: column; gap: 6px; }
.pr-label { font-size: 12.5px; font-weight: 700; color: var(--ink); text-transform: uppercase; letter-spacing: 0.5px; }
.pr-input {
    border: 1.5px solid var(--line); border-radius: 10px;
    padding: 11px 14px; font-family: 'Inter', sans-serif; font-size: 14px;
    background: #fff; color: var(--ink); outline: none; transition: border-color .2s;
    width: 100%; box-sizing: border-box;
}
.pr-input:focus { border-color: var(--brass); box-shadow: 0 0 0 3px rgba(200,155,60,0.12); }
.pr-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2314213D' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
.pr-hint { font-size: 11.5px; color: var(--soft); }

.pr-submit-btn {
    padding: 13px; background: var(--forest); color: #fff;
    border: none; border-radius: 10px; font-family: 'Inter', sans-serif;
    font-size: 15px; font-weight: 700; cursor: pointer; transition: background .2s;
    letter-spacing: 0.2px;
}
.pr-submit-btn:hover:not(:disabled) { background: var(--forest-dark); }
.pr-submit-btn:disabled { opacity: 0.55; cursor: not-allowed; }

.pr-error {
    margin-top: 16px; padding: 14px; background: #fdf2f2;
    border: 1px solid #fca5a5; border-radius: 10px;
    color: #b91c1c; font-size: 13.5px; display: flex; gap: 8px; align-items: flex-start;
}
.pr-error-icon { flex-shrink: 0; }

.pr-info-box {
    margin-top: 20px; padding: 14px; background: rgba(31,77,58,0.06);
    border: 1px solid rgba(31,77,58,0.18); border-radius: 10px; font-size: 13px;
    color: var(--soft); line-height: 1.6;
}
.pr-info-title { font-weight: 600; color: var(--forest); margin-bottom: 4px; }
.pr-info-box p { margin: 0; }

/* Empty state */
.pr-empty {
    text-align: center; padding: 80px 24px;
    border: 1.5px dashed var(--line); border-radius: 16px;
    color: var(--soft);
}
.pr-empty-icon { font-size: 48px; margin-bottom: 16px; }
.pr-empty h3 { font-size: 20px; font-weight: 600; margin: 0 0 8px; color: var(--ink); }
.pr-empty p { font-size: 14px; line-height: 1.7; max-width: 380px; margin: 0 auto; }

/* Marksheet */
.pr-marksheet-wrap { }
.pr-marksheet-actions { display: flex; justify-content: flex-end; margin-bottom: 16px; }
.pr-print-btn {
    padding: 9px 20px; background: var(--ink); color: #fff;
    border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: background .2s;
}
.pr-print-btn:hover { background: var(--forest); }

.pr-marksheet {
    background: #fff; border: 1px solid var(--line);
    border-radius: 16px; overflow: hidden;
    box-shadow: 0 12px 28px -16px rgba(20,33,61,0.3);
}

.pr-ms-header {
    background: var(--ink); color: #fff;
    padding: 24px 28px; display: flex; align-items: center; gap: 18px;
}
.pr-ms-crest {
    width: 52px; height: 52px; border-radius: 50%; flex-shrink: 0;
    background: radial-gradient(circle at 35% 30%, var(--brass) 0%, var(--brass-dark) 70%);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Fraunces', serif; font-weight: 700; color: var(--ink); font-size: 20px;
}
.pr-ms-header h2 { font-size: 20px; font-weight: 700; margin: 0 0 4px; }
.pr-ms-header p { font-size: 11px; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 1.5px; margin: 0; }

.pr-ms-status-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 28px; font-size: 14px; font-weight: 600;
}
.pr-ms-status-bar--pass { background: #dcfce7; color: #15803d; }
.pr-ms-status-bar--fail { background: #fee2e2; color: #b91c1c; }
.pr-ms-status-badge { font-size: 13px; font-weight: 800; letter-spacing: 0.5px; }

.pr-ms-bio { padding: 20px 28px; border-bottom: 1px solid var(--line); }
.pr-ms-bio-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 560px) { .pr-ms-bio-grid { grid-template-columns: 1fr; } }
.pr-ms-bio-item { display: flex; flex-direction: column; gap: 2px; }
.pr-ms-bio-label { font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--soft); font-weight: 600; }
.pr-ms-bio-value { font-size: 15px; font-weight: 700; color: var(--ink); }
.pr-mono { font-family: 'IBM Plex Mono', monospace; }

.pr-ms-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.pr-ms-table th {
    background: #f0ede8; padding: 10px 14px; text-align: left;
    font-weight: 700; font-size: 11.5px; text-transform: uppercase;
    letter-spacing: 0.5px; color: var(--soft);
    border-bottom: 2px solid var(--line);
}
.pr-ms-table th:not(:first-child) { text-align: center; }
.pr-ms-table td { padding: 11px 14px; border-bottom: 1px solid var(--line); color: var(--ink); }
.pr-ms-table-fail { background: #fff8f8; }
.pr-ms-subject { font-weight: 600; }
.pr-ms-center { text-align: center; }
.pr-ms-score { font-weight: 700; font-size: 14px; }
.pr-ms-grade { font-weight: 800; font-size: 14px; color: var(--forest); }
.pr-ms-grade--fail { color: var(--urgent); }

.pr-ms-summary {
    display: flex; align-items: flex-end; justify-content: space-between;
    padding: 20px 28px; border-top: 2px solid var(--line); gap: 16px;
    flex-wrap: wrap;
}
.pr-ms-remarks-label { display: block; font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--soft); margin-bottom: 4px; }
.pr-ms-remarks em { font-size: 13.5px; color: var(--ink); }
.pr-ms-gpa-box { display: flex; gap: 20px; align-items: flex-end; }
.pr-ms-gpa, .pr-ms-grade-final { text-align: right; }
.pr-ms-gpa-label { display: block; font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--soft); }
.pr-ms-gpa-value { font-family: 'IBM Plex Mono', monospace; font-size: 28px; font-weight: 700; color: var(--ink); line-height: 1; }
.pr-ms-grade-final-value { font-family: 'Fraunces', serif; font-size: 36px; font-weight: 700; color: var(--forest); line-height: 1; }

.pr-ms-official {
    text-align: center; padding: 10px 28px 14px;
    font-family: 'IBM Plex Mono', monospace; font-size: 10px;
    color: var(--soft); border-top: 1px dashed var(--line);
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
    background: var(--sv-ink, #14213D);
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
    color: var(--brass, #C89B3C);
}
.sv-mobile-cta {
    margin-top: 16px;
    display: block;
    text-align: center;
    padding: 10px;
    background: var(--brass, #C89B3C);
    color: var(--ink, #14213D);
    border-radius: 8px;
    font-weight: 700;
    transition: background 0.2s;
}
.sv-mobile-cta:hover {
    background: var(--brass-dark, #A87F2B);
    color: #fff;
}

/* Footer */
.pr-footer { background: var(--forest-dark); color: rgba(255,255,255,0.7); padding: 24px; z-index: 1; position: relative; }
.pr-footer-inner {
    max-width: 1200px; margin: 0 auto;
    display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; font-size: 12.5px;
}
.pr-footer-inner strong { color: #fff; }

@media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
</style>
