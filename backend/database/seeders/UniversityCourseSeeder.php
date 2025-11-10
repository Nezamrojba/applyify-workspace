<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UniversityCourseSeeder extends Seeder
{
    public function run(): void
    {
        $malaysiaId = DB::table('countries')
            ->where('code', 'MY')
            ->value('id');

        if (!$malaysiaId) {
            $malaysiaId = DB::table('countries')->insertGetId([
                'name' => 'Malaysia',
                'code' => 'MY',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $encode = static fn(array $value) => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        DB::transaction(function () use ($malaysiaId, $encode) {
            DB::table('course_fee_structures')->delete();
            DB::table('courses')->delete();
            DB::table('universities')->delete();

            $universities = [
                [
                    'name' => 'City University Malaysia',
                    'name_ar' => 'جامعة سيتي ماليزيا',
                    'location' => 'Petaling Jaya, Selangor',
                    'location_ar' => 'بيتالينغ جايا، سيلانغور',
                    'established' => 1984,
                    'website' => 'https://city.edu.my',
                    'type_en' => 'Private University',
                    'type_ar' => 'جامعة خاصة',
                    'summary_en' => 'Industry-driven programmes with flexible online learning modes and strong employer partnerships across Klang Valley.',
                    'summary_ar' => 'برامج تركز على سوق العمل مع أنماط تعلم مرنة عبر الإنترنت وشراكات مع أصحاب العمل في وادي كلانغ.',
                    'courses' => [
                        [
                            'name' => 'Bachelor of Information Technology (Hons) (Online Learning)',
                            'name_ar' => 'بكالوريوس تكنولوجيا المعلومات (مع مرتبة الشرف) (تعلم إلكتروني)',
                            'code' => 'CITY-BIT-ODL',
                            'level' => 'Bachelor',
                            'duration_months' => 36,
                            'total_years' => 3,
                            'total_tuition_fees' => 42000.00,
                            'procedure_fees' => 950.00,
                            'acceptance_percent' => 78,
                            'payment_method' => 'Online or semester-based payments via City FlexPay',
                            'allow_installments' => true,
                            'details' => 'Accredited three-year IT degree delivered fully online with weekend live tutorials. Includes software engineering, analytics, cloud infrastructure, and a 12-week virtual industry attachment.',
                            'i18n' => [
                                'name' => [
                                    'en' => 'Bachelor of Information Technology (Hons) (Online Learning)',
                                    'ar' => 'بكالوريوس تكنولوجيا المعلومات (مع مرتبة الشرف) (تعلم إلكتروني)',
                                ],
                                'overview' => [
                                    'en' => [
                                        '36-month online programme with specialisations in software engineering, data analytics, and networking.',
                                        'Includes a 12-week remote industrial training module with tech partners in Klang Valley.',
                                    ],
                                    'ar' => [
                                        'برنامج عبر الإنترنت مدته 36 شهرًا مع تخصصات في هندسة البرمجيات وتحليلات البيانات والشبكات.',
                                        'يشمل تدريباً صناعياً عن بُعد لمدة 12 أسبوعاً مع شركاء تقنيين في وادي كلانغ.',
                                    ],
                                ],
                                'outcomes' => [
                                    'en' => [
                                        'Software Engineering',
                                        'Database Administration',
                                        'IT Project Coordination',
                                    ],
                                    'ar' => [
                                        'هندسة البرمجيات',
                                        'إدارة قواعد البيانات',
                                        'تنسيق مشاريع تقنية المعلومات',
                                    ],
                                ],
                                'requirements' => [
                                    'en' => [
                                        'STPM / A-Level with two passes or computing diploma (MQA Level 4).',
                                        'IELTS 5.0 or MUET Band 3 (waived for full English schooling).',
                                        'Interview for applicants without prior IT background.',
                                    ],
                                    'ar' => [
                                        'شهادة STPM أو A-Level بحد أدنى نجاحين أو دبلوم حوسبة (المستوى الرابع من MQA).',
                                        'IELTS 5.0 أو MUET Band 3 (معفى للدارسين باللغة الإنجليزية).',
                                        'مقابلة للمتقدمين من غير الخلفية التقنية.',
                                    ],
                                ],
                                'language' => [
                                    'en' => 'English',
                                    'ar' => 'الإنجليزية',
                                ],
                                'accreditation' => [
                                    'en' => 'MQA/PA 12078',
                                    'ar' => 'اعتماد MQA/PA 12078',
                                ],
                                'intakes' => [
                                    'en' => ['January', 'May', 'September'],
                                    'ar' => ['يناير', 'مايو', 'سبتمبر'],
                                ],
                            ],
                            'fee_structure' => [
                                'program_code' => 'CITY-BIT-ODL-2025',
                                'intake' => 'January / May / September 2025',
                                'tuition_fee_per_credit_hour' => 380.00,
                                'semesters' => [
                                    [
                                        'semester_type' => 'Year 1 Semester 1',
                                        'academic_period' => 'Jan – Apr 2025',
                                        'credit_hours' => 15,
                                        'tuition_fee' => 5800,
                                        'library_fee' => 150,
                                        'student_club_fee' => 90,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Digital learning platform access', 'amount' => 250],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 1 Semester 2',
                                        'academic_period' => 'May – Aug 2025',
                                        'credit_hours' => 16,
                                        'tuition_fee' => 5900,
                                        'library_fee' => 150,
                                        'student_club_fee' => 90,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Software subscription bundle', 'amount' => 280],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 2 Semester 1',
                                        'academic_period' => 'Sep – Dec 2025',
                                        'credit_hours' => 15,
                                        'tuition_fee' => 6000,
                                        'library_fee' => 150,
                                        'student_club_fee' => 90,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Virtual lab access', 'amount' => 300],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 2 Semester 2',
                                        'academic_period' => 'Jan – Apr 2026',
                                        'credit_hours' => 16,
                                        'tuition_fee' => 6000,
                                        'library_fee' => 150,
                                        'student_club_fee' => 90,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Industry certification voucher', 'amount' => 350],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 3 Semester 1',
                                        'academic_period' => 'May – Aug 2026',
                                        'credit_hours' => 15,
                                        'tuition_fee' => 6050,
                                        'library_fee' => 150,
                                        'student_club_fee' => 90,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Final year project supervision', 'amount' => 320],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 3 Semester 2',
                                        'academic_period' => 'Sep – Dec 2026',
                                        'credit_hours' => 14,
                                        'tuition_fee' => 6050,
                                        'library_fee' => 150,
                                        'student_club_fee' => 90,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Internship monitoring', 'amount' => 240],
                                        ],
                                    ],
                                ],
                                'one_time_fees' => [
                                    ['name' => 'Application & registration fee', 'amount' => 750, 'refundable' => false],
                                    ['name' => 'E-learning starter kit', 'amount' => 450, 'refundable' => false],
                                ],
                                'discounts' => [
                                    [
                                        'description' => 'Early bird (enrol before 31 Dec 2024) – 5% tuition waiver',
                                        'percentage' => 5,
                                    ],
                                    [
                                        'description' => 'Alumni sibling rebate – RM800 one-off',
                                        'percentage' => null,
                                    ],
                                ],
                                'payment_methods' => [
                                    'en' => 'Pay per semester or via monthly auto-debit (0% interest). International students may pay via Flywire.',
                                    'ar' => 'الدفع لكل فصل دراسي أو عبر خصم شهري آلي بدون فوائد. يمكن للطلاب الدوليين الدفع عبر Flywire.',
                                ],
                                'policies' => [
                                    'en' => [
                                        'All tuition fees are quoted in MYR and reviewed annually.',
                                        'Installment plans require an approved standing instruction.',
                                    ],
                                    'ar' => [
                                        'جميع الرسوم الدراسية بالرينجيت الماليزي ويتم مراجعتها سنوياً.',
                                        'خطة الأقساط تتطلب تفويض خصم بنكي معتمد.',
                                    ],
                                ],
                                'i18n' => [
                                    'notes' => [
                                        'en' => 'Accommodation, insurance, and visa processing are billed separately if applicable.',
                                        'ar' => 'السكن والتأمين ومعالجة التأشيرات تُحصّل بشكل منفصل عند الحاجة.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Diploma in Mechanical Engineering (Online)',
                            'name_ar' => 'دبلوم الهندسة الميكانيكية (عبر الإنترنت)',
                            'code' => 'CITY-DME-ONLINE',
                            'level' => 'Diploma',
                            'duration_months' => 36,
                            'total_years' => 3,
                            'total_tuition_fees' => 29800.00,
                            'procedure_fees' => 850.00,
                            'acceptance_percent' => 74,
                            'payment_method' => 'Yearly tuition with semester instalments',
                            'allow_installments' => true,
                            'details' => 'Three-year ETAC-accredited diploma delivered in blended online mode with compulsory weekend workshops. Focus on thermofluids, CAD, and sustainable manufacturing.',
                            'i18n' => [
                                'name' => [
                                    'en' => 'Diploma in Mechanical Engineering (Online)',
                                    'ar' => 'دبلوم الهندسة الميكانيكية (عبر الإنترنت)',
                                ],
                                'overview' => [
                                    'en' => [
                                        'Programme aligns with ETAC standards and provides weekend lab intensives in Kuala Lumpur campus.',
                                        'Online simulations supplement hands-on sessions in manufacturing, thermofluids, and CAD.',
                                    ],
                                    'ar' => [
                                        'برنامج مطابق لمعايير ETAC ويقدم جلسات مكثفة في الحرم الجامعي بكوالالمبور نهاية الأسبوع.',
                                        'محاكاة عبر الإنترنت تدعم الجلسات العملية في التصنيع والتدفق الحراري والتصميم باستخدام الحاسوب.',
                                    ],
                                ],
                                'outcomes' => [
                                    'en' => [
                                        'Assistant Mechanical Engineer',
                                        'Maintenance Technician',
                                        'CAD Technician',
                                    ],
                                    'ar' => [
                                        'مساعد مهندس ميكانيكي',
                                        'فني صيانة',
                                        'فني تصميم باستخدام الحاسوب',
                                    ],
                                ],
                                'requirements' => [
                                    'en' => [
                                        'SPM with 3 credits including Mathematics and Science OR SKM Level 3 in related field.',
                                        'Pass in English at SPM or equivalent (MUET Band 3 recommended).',
                                        'Working adults with technical experience may apply via APEL A.',
                                    ],
                                    'ar' => [
                                        'شهادة SPM بثلاث مواد على الأقل بما فيها الرياضيات والعلوم أو SKM المستوى الثالث في مجال ذي صلة.',
                                        'النجاح في مادة اللغة الإنجليزية في SPM أو ما يعادلها (يفضل MUET Band 3).',
                                        'يُمكن للموظفين ذوي الخبرة التقنية التقديم عبر مسار APEL A.',
                                    ],
                                ],
                                'language' => [
                                    'en' => 'English',
                                    'ar' => 'الإنجليزية',
                                ],
                                'accreditation' => [
                                    'en' => 'ETAC / MQA/FA 8747',
                                    'ar' => 'اعتماد ETAC / MQA/FA 8747',
                                ],
                                'intakes' => [
                                    'en' => ['January', 'July'],
                                    'ar' => ['يناير', 'يوليو'],
                                ],
                            ],
                            'fee_structure' => [
                                'program_code' => 'CITY-DME-ONLINE-2025',
                                'intake' => 'January / July 2025',
                                'tuition_fee_per_credit_hour' => 310.00,
                                'semesters' => [
                                    [
                                        'semester_type' => 'Year 1 Semester 1',
                                        'academic_period' => 'Jan – Apr 2025',
                                        'credit_hours' => 13,
                                        'tuition_fee' => 3600,
                                        'library_fee' => 120,
                                        'student_club_fee' => 80,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Workshop consumables', 'amount' => 220],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 1 Semester 2',
                                        'academic_period' => 'May – Aug 2025',
                                        'credit_hours' => 13,
                                        'tuition_fee' => 3600,
                                        'library_fee' => 120,
                                        'student_club_fee' => 80,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'CAD licence', 'amount' => 260],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 2 Semester 1',
                                        'academic_period' => 'Jan – Apr 2026',
                                        'credit_hours' => 14,
                                        'tuition_fee' => 3650,
                                        'library_fee' => 120,
                                        'student_club_fee' => 80,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Materials testing kit', 'amount' => 280],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 2 Semester 2',
                                        'academic_period' => 'May – Aug 2026',
                                        'credit_hours' => 14,
                                        'tuition_fee' => 3650,
                                        'library_fee' => 120,
                                        'student_club_fee' => 80,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Rapid prototyping materials', 'amount' => 260],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 3 Semester 1',
                                        'academic_period' => 'Jan – Apr 2027',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 3700,
                                        'library_fee' => 120,
                                        'student_club_fee' => 80,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Capstone project materials', 'amount' => 320],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 3 Semester 2',
                                        'academic_period' => 'May – Aug 2027',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 3700,
                                        'library_fee' => 120,
                                        'student_club_fee' => 80,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Industry placement support', 'amount' => 280],
                                        ],
                                    ],
                                ],
                                'one_time_fees' => [
                                    ['name' => 'Engineering lab deposit (refundable)', 'amount' => 600, 'refundable' => true],
                                    ['name' => 'Student activity & insurance', 'amount' => 550, 'refundable' => false],
                                ],
                                'discounts' => [
                                    [
                                        'description' => 'STEM excellence scholarship – up to 20% tuition rebate',
                                        'percentage' => 20,
                                    ],
                                    [
                                        'description' => 'Full upfront payment bursary – 2% off semester tuition',
                                        'percentage' => 2,
                                    ],
                                ],
                                'payment_methods' => [
                                    'en' => 'Annual payment with four interest-free instalments or monthly auto-debit (subject to approval).',
                                    'ar' => 'دفع سنوي مع أربعة أقساط بدون فوائد أو خصم شهري آلي (يخضع للموافقة).',
                                ],
                                'policies' => [
                                    'en' => [
                                        'Personal protective equipment (PPE) is not included and must be purchased separately.',
                                        'Weekend workshop attendance of at least 80% is required for assessment.',
                                    ],
                                    'ar' => [
                                        'معدات الحماية الشخصية غير مشمولة ويجب شراؤها بشكل منفصل.',
                                        'يتطلب حضور ورش نهاية الأسبوع بنسبة لا تقل عن 80٪ للتقييم.',
                                    ],
                                ],
                                'i18n' => [
                                    'notes' => [
                                        'en' => 'Lab consumables for optional on-campus intensives are billed on actual usage.',
                                        'ar' => 'مواد المختبر للجلسات المكثفة الاختيارية في الحرم تُحتسب حسب الاستهلاك الفعلي.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Wawasan Open University',
                    'name_ar' => 'جامعة واسووسان المفتوحة',
                    'location' => 'George Town, Penang',
                    'location_ar' => 'جورج تاون، بينانغ',
                    'established' => 2006,
                    'website' => 'https://wou.edu.my',
                    'type_en' => 'Private Open University',
                    'type_ar' => 'جامعة مفتوحة خاصة',
                    'summary_en' => 'Malaysia’s established open and distance learning university offering flexible engineering technology programmes with pay-per-semester fees.',
                    'summary_ar' => 'جامعة ماليزية رائدة في التعليم المفتوح عن بعد تقدم برامج تقنية مرنة مع رسوم تدفع لكل فصل.',
                    'courses' => [
                        [
                            'name' => 'Bachelor of Technology (Hons) in Construction Management (ODL)',
                            'name_ar' => 'بكالوريوس التكنولوجيا (مع مرتبة الشرف) في إدارة الإنشاءات (تعليم مفتوح)',
                            'code' => 'WOU-BTCM-ODL',
                            'level' => 'Bachelor',
                            'duration_months' => 48,
                            'total_years' => 4,
                            'total_tuition_fees' => 34800.00,
                            'procedure_fees' => 600.00,
                            'acceptance_percent' => 68,
                            'payment_method' => 'Per semester tuition with flexible payment plan',
                            'allow_installments' => true,
                            'details' => 'Open distance learning degree covering construction technology, project control, and BIM applications. Tutorials held online with monthly regional workshops.',
                            'i18n' => [
                                'name' => [
                                    'en' => 'Bachelor of Technology (Hons) in Construction Management (ODL)',
                                    'ar' => 'بكالوريوس التكنولوجيا (مع مرتبة الشرف) في إدارة الإنشاءات (تعليم مفتوح)',
                                ],
                                'overview' => [
                                    'en' => [
                                        'Curriculum mapped to Malaysian Board of Technologists requirements with project-based learning each semester.',
                                        'Integrated assignments using current Malaysian construction case studies and BIM platforms.',
                                    ],
                                    'ar' => [
                                        'منهج يتوافق مع متطلبات مجلس التقنيين الماليزي مع تعلم قائم على المشاريع في كل فصل.',
                                        'واجبات متكاملة تستخدم دراسات حالة حديثة في الإنشاءات الماليزية ومنصات BIM.',
                                    ],
                                ],
                                'outcomes' => [
                                    'en' => [
                                        'Site & Project Coordinator',
                                        'Construction Planning Executive',
                                        'BIM Coordinator',
                                    ],
                                    'ar' => [
                                        'منسق موقع ومشاريع',
                                        'تنفيذي تخطيط إنشاءات',
                                        'منسق BIM',
                                    ],
                                ],
                                'requirements' => [
                                    'en' => [
                                        'STPM/A-Level with two passes including Mathematics/Science OR recognised diploma (MQA Level 4).',
                                        'MUET Band 3 or IELTS 5.5 for international applicants.',
                                        'Adults aged 21+ with relevant experience may enter via APEL A.',
                                    ],
                                    'ar' => [
                                        'شهادة STPM/A-Level مع نجاحين على الأقل بما فيها الرياضيات أو العلوم أو دبلوم معترف به (المستوى الرابع من MQA).',
                                        'MUET Band 3 أو IELTS 5.5 للمتقدمين الدوليين.',
                                        'البالغون فوق 21 عاماً بخبرة ذات صلة يمكنهم الالتحاق عبر APEL A.',
                                    ],
                                ],
                                'language' => [
                                    'en' => 'English',
                                    'ar' => 'الإنجليزية',
                                ],
                                'accreditation' => [
                                    'en' => 'MQA/PA 12187',
                                    'ar' => 'اعتماد MQA/PA 12187',
                                ],
                                'intakes' => [
                                    'en' => ['January', 'May', 'September'],
                                    'ar' => ['يناير', 'مايو', 'سبتمبر'],
                                ],
                            ],
                            'fee_structure' => [
                                'program_code' => 'WOU-BTCM-2025',
                                'intake' => 'January / May / September 2025',
                                'tuition_fee_per_credit_hour' => 285.00,
                                'semesters' => [
                                    [
                                        'semester_type' => 'Year 1 Semester 1',
                                        'academic_period' => 'Jan – Apr 2025',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4350,
                                        'library_fee' => 100,
                                        'student_club_fee' => 60,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Digital module pack', 'amount' => 150],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 1 Semester 2',
                                        'academic_period' => 'May – Aug 2025',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4350,
                                        'library_fee' => 100,
                                        'student_club_fee' => 60,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Regional workshop facilitation', 'amount' => 200],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 2 Semester 1',
                                        'academic_period' => 'Sep – Dec 2025',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4350,
                                        'library_fee' => 100,
                                        'student_club_fee' => 60,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'BIM platform subscription', 'amount' => 180],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 2 Semester 2',
                                        'academic_period' => 'Jan – Apr 2026',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4350,
                                        'library_fee' => 100,
                                        'student_club_fee' => 60,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Site visit logistics', 'amount' => 180],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 3 Semester 1',
                                        'academic_period' => 'May – Aug 2026',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4350,
                                        'library_fee' => 100,
                                        'student_club_fee' => 60,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Professional seminar series', 'amount' => 160],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 3 Semester 2',
                                        'academic_period' => 'Sep – Dec 2026',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4350,
                                        'library_fee' => 100,
                                        'student_club_fee' => 60,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Capstone supervision & assessment', 'amount' => 190],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 4 Semester 1',
                                        'academic_period' => 'Jan – Apr 2027',
                                        'credit_hours' => 10,
                                        'tuition_fee' => 3300,
                                        'library_fee' => 100,
                                        'student_club_fee' => 60,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Industrial training monitoring', 'amount' => 220],
                                        ],
                                    ],
                                ],
                                'one_time_fees' => [
                                    ['name' => 'Enrolment fee', 'amount' => 450, 'refundable' => false],
                                    ['name' => 'Student development levy', 'amount' => 320, 'refundable' => false],
                                ],
                                'discounts' => [
                                    [
                                        'description' => 'WOU bursary – RM600 rebate for each semester paid in full before census date.',
                                        'percentage' => null,
                                    ],
                                    [
                                        'description' => 'ODL Excellence Award – up to 20% for outstanding STPM / Diploma GPA.',
                                        'percentage' => 20,
                                    ],
                                ],
                                'payment_methods' => [
                                    'en' => 'Students may pay per semester, via three-part instalments each semester, or monthly FPX autopay.',
                                    'ar' => 'يمكن للطلاب الدفع لكل فصل، أو عبر ثلاث دفعات خلال الفصل، أو عبر خصم شهري إلكتروني FPX.',
                                ],
                                'policies' => [
                                    'en' => [
                                        'Fees exclude external professional body registrations and overseas field trips.',
                                        'International students must maintain valid iKad and medical insurance.',
                                    ],
                                    'ar' => [
                                        'الرسوم لا تشمل التسجيل في الهيئات المهنية الخارجية أو الرحلات الميدانية خارج ماليزيا.',
                                        'يجب على الطلاب الدوليين الحفاظ على بطاقة iKad سارية وتأمين طبي.',
                                    ],
                                ],
                                'i18n' => [
                                    'notes' => [
                                        'en' => 'Learning materials are provided digitally; printed modules can be purchased separately.',
                                        'ar' => 'يتم توفير المواد التعليمية رقمياً ويمكن شراء النسخ المطبوعة بشكل منفصل.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Open University Malaysia',
                    'name_ar' => 'الجامعة المفتوحة الماليزية',
                    'location' => 'Kuala Lumpur',
                    'location_ar' => 'كوالالمبور',
                    'established' => 2000,
                    'website' => 'https://www.oum.edu.my',
                    'type_en' => 'Private Open University',
                    'type_ar' => 'جامعة مفتوحة خاصة',
                    'summary_en' => 'Malaysia’s pioneer open and distance learning university offering working adults flexible pathways with nationwide learning centres.',
                    'summary_ar' => 'جامعة رائدة في التعليم المفتوح عن بعد في ماليزيا توفر مسارات مرنة للموظفين مع مراكز تعلم في جميع أنحاء البلاد.',
                    'courses' => [
                        [
                            'name' => 'Bachelor of Electronics Engineering Technology (Industrial Automation) with Honours',
                            'name_ar' => 'بكالوريوس تكنولوجيا الهندسة الإلكترونية (الأتمتة الصناعية) مع مرتبة الشرف',
                            'code' => 'OUM-BEET-IA',
                            'level' => 'Bachelor',
                            'duration_months' => 48,
                            'total_years' => 4,
                            'total_tuition_fees' => 37200.00,
                            'procedure_fees' => 700.00,
                            'acceptance_percent' => 72,
                            'payment_method' => 'Tri-semester payment schedule with monthly autopay option',
                            'allow_installments' => true,
                            'details' => 'Bachelor’s degree focused on smart electronics, industrial automation, and IoT integration. Blended mode with online tutorials plus compulsory weekend practical sessions.',
                            'i18n' => [
                                'name' => [
                                    'en' => 'Bachelor of Electronics Engineering Technology (Industrial Automation) with Honours',
                                    'ar' => 'بكالوريوس تكنولوجيا الهندسة الإلكترونية (الأتمتة الصناعية) مع مرتبة الشرف',
                                ],
                                'overview' => [
                                    'en' => [
                                        'Industry-driven curriculum covering circuit design, automation, robotics, and smart manufacturing.',
                                        'Includes remote lab simulations and compulsory hands-on practical blocks at OUM learning centres.',
                                    ],
                                    'ar' => [
                                        'منهج موجه للصناعة يشمل تصميم الدوائر والأتمتة والروبوتات والتصنيع الذكي.',
                                        'يشمل محاكاة مختبرية عن بعد وجلسات عملية إلزامية في مراكز الجامعة.',
                                    ],
                                ],
                                'outcomes' => [
                                    'en' => [
                                        'Automation Engineer',
                                        'PLC & Control Specialist',
                                        'IoT Systems Integrator',
                                    ],
                                    'ar' => [
                                        'مهندس أتمتة',
                                        'متخصص في أنظمة التحكم PLC',
                                        'مُدمج لأنظمة إنترنت الأشياء',
                                    ],
                                ],
                                'requirements' => [
                                    'en' => [
                                        'STPM/A-Level with two passes including Mathematics/Physics OR recognised diploma (MQA Level 4).',
                                        'MUET Band 3 or IELTS 5.5 for international applicants.',
                                        'Working adults aged 21+ with relevant experience may enter via APEL A.',
                                    ],
                                    'ar' => [
                                        'شهادة STPM/A-Level مع نجاحين بما فيها الرياضيات أو الفيزياء أو دبلوم معترف به (المستوى الرابع من MQA).',
                                        'MUET Band 3 أو IELTS 5.5 للمتقدمين الدوليين.',
                                        'البالغون فوق 21 عاماً وخبرة ذات صلة يمكنهم الالتحاق عبر APEL A.',
                                    ],
                                ],
                                'language' => [
                                    'en' => 'English',
                                    'ar' => 'الإنجليزية',
                                ],
                                'accreditation' => [
                                    'en' => 'MQA/PA 12211 & ETAC',
                                    'ar' => 'اعتماد MQA/PA 12211 و ETAC',
                                ],
                                'intakes' => [
                                    'en' => ['March', 'July', 'November'],
                                    'ar' => ['مارس', 'يوليو', 'نوفمبر'],
                                ],
                            ],
                            'fee_structure' => [
                                'program_code' => 'OUM-BEET-IA-2025',
                                'intake' => 'March / July / November 2025',
                                'tuition_fee_per_credit_hour' => 320.00,
                                'semesters' => [
                                    [
                                        'semester_type' => 'Year 1 Semester 1',
                                        'academic_period' => 'Mar – Jun 2025',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4650,
                                        'library_fee' => 90,
                                        'student_club_fee' => 50,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Remote lab access licence', 'amount' => 200],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 1 Semester 2',
                                        'academic_period' => 'Jul – Oct 2025',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4650,
                                        'library_fee' => 90,
                                        'student_club_fee' => 50,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Automation toolkit (loaned)', 'amount' => 240],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 2 Semester 1',
                                        'academic_period' => 'Nov 2025 – Feb 2026',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4650,
                                        'library_fee' => 90,
                                        'student_club_fee' => 50,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Industry certification voucher (TIA Portal)', 'amount' => 260],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 2 Semester 2',
                                        'academic_period' => 'Mar – Jun 2026',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4650,
                                        'library_fee' => 90,
                                        'student_club_fee' => 50,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Practical block facilitation', 'amount' => 220],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 3 Semester 1',
                                        'academic_period' => 'Jul – Oct 2026',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4650,
                                        'library_fee' => 90,
                                        'student_club_fee' => 50,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Embedded systems kit', 'amount' => 240],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 3 Semester 2',
                                        'academic_period' => 'Nov 2026 – Feb 2027',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4650,
                                        'library_fee' => 90,
                                        'student_club_fee' => 50,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Industry networking & IoT summit', 'amount' => 220],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 4 Semester 1',
                                        'academic_period' => 'Mar – Jun 2027',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4650,
                                        'library_fee' => 90,
                                        'student_club_fee' => 50,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Capstone supervision & remote lab usage', 'amount' => 260],
                                        ],
                                    ],
                                    [
                                        'semester_type' => 'Year 4 Semester 2',
                                        'academic_period' => 'Jul – Oct 2027',
                                        'credit_hours' => 12,
                                        'tuition_fee' => 4650,
                                        'library_fee' => 90,
                                        'student_club_fee' => 50,
                                        'ikad_fee' => 0,
                                        'visa_processing_fee' => 0,
                                        'medical_insurance_fee' => 0,
                                        'medical_examination_fee' => 0,
                                        'custom_fees' => [
                                            ['name' => 'Industrial training monitoring', 'amount' => 260],
                                        ],
                                    ],
                                ],
                                'one_time_fees' => [
                                    ['name' => 'Application & processing fee', 'amount' => 450, 'refundable' => false],
                                    ['name' => 'Resource & lab access fee', 'amount' => 420, 'refundable' => false],
                                ],
                                'discounts' => [
                                    [
                                        'description' => 'Launch bursary for first 100 ODL learners – 10% tuition reduction.',
                                        'percentage' => 10,
                                    ],
                                    [
                                        'description' => 'OUM Merit Scholarship – RM1,000 per year for CGPA ≥3.30.',
                                        'percentage' => null,
                                    ],
                                ],
                                'payment_methods' => [
                                    'en' => 'Tri-semester schedule or monthly payment plan via JomPay/FPX with no interest.',
                                    'ar' => 'جدول بثلاثة فصول أو خطة دفع شهرية عبر JomPay/FPX بدون فوائد.',
                                ],
                                'policies' => [
                                    'en' => [
                                        'Lab access deposit of RM400 is refundable upon programme completion.',
                                        'International students must subscribe to OUM medical insurance each year.',
                                    ],
                                    'ar' => [
                                        'وديعة استخدام المختبر بقيمة 400 رينجيت قابلة للاسترداد عند إتمام البرنامج.',
                                        'يجب على الطلاب الدوليين الاشتراك في التأمين الطبي الخاص بالجامعة كل عام.',
                                    ],
                                ],
                                'i18n' => [
                                    'notes' => [
                                        'en' => 'Fees include access to remote laboratories and IoT cloud simulation environments.',
                                        'ar' => 'تشمل الرسوم الوصول إلى المختبرات عن بُعد وبيئات محاكاة إنترنت الأشياء السحابية.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            foreach ($universities as $university) {
                $universityId = DB::table('universities')->insertGetId([
                    'name' => $university['name'],
                    'country_id' => $malaysiaId,
                    'country' => 'Malaysia',
                    'is_active' => true,
                    'i18n' => $encode([
                        'name' => [
                            'en' => $university['name'],
                            'ar' => $university['name_ar'],
                        ],
                        'location' => [
                            'en' => $university['location'],
                            'ar' => $university['location_ar'],
                        ],
                        'type' => [
                            'en' => $university['type_en'],
                            'ar' => $university['type_ar'],
                        ],
                        'summary' => [
                            'en' => $university['summary_en'],
                            'ar' => $university['summary_ar'],
                        ],
                        'website' => $university['website'],
                        'established' => $university['established'],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($university['courses'] as $course) {
                    $courseId = DB::table('courses')->insertGetId([
                        'university_id' => $universityId,
                        'name' => $course['name'],
                        'code' => $course['code'],
                        'level' => $course['level'],
                        'acceptance_percent' => $course['acceptance_percent'],
                        'duration_months' => $course['duration_months'],
                        'i18n' => $encode($course['i18n']),
                        'is_active' => true,
                        'details' => $course['details'],
                        'total_tuition_fees' => $course['total_tuition_fees'],
                        'procedure_fees' => $course['procedure_fees'],
                        'payment_method' => $course['payment_method'],
                        'allow_installments' => $course['allow_installments'],
                        'total_years' => $course['total_years'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (!empty($course['fee_structure'])) {
                        $fee = $course['fee_structure'];

                        DB::table('course_fee_structures')->insert([
                            'course_id' => $courseId,
                            'program_code' => $fee['program_code'] ?? null,
                            'intake' => $fee['intake'] ?? null,
                            'tuition_fee_per_credit_hour' => $fee['tuition_fee_per_credit_hour'] ?? 0,
                            'semesters' => $encode($fee['semesters'] ?? []),
                            'one_time_fees' => $encode($fee['one_time_fees'] ?? []),
                            'discounts' => $encode($fee['discounts'] ?? []),
                            'payment_methods' => $encode($fee['payment_methods'] ?? []),
                            'policies' => $encode($fee['policies'] ?? []),
                            'i18n' => $encode($fee['i18n'] ?? []),
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }
}
