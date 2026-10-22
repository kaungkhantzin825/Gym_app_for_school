<?php

namespace Database\Seeders;

use App\Models\TutorialVideo;
use Illuminate\Database\Seeder;

class TutorialVideoSeeder extends Seeder
{
    public function run(): void
    {
        TutorialVideo::query()->delete();

        $tutorials = [
            [
                'title' => 'Incline Bench Press* Dumbbell',
                'description' => 'Target the upper clavicular head of the pectoralis major. Focus on deep stretch at the bottom and hard contraction at peak.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'chest',
                'focus_areas' => ['Chest', 'Shoulder', 'Triceps'],
                'equipment' => 'Dumbbell',
                'how_to_do' => "1. Set an adjustable bench to an incline of 30-45 degrees.\n2. Sit back with a pair of dumbbells resting on your thighs.\n3. Kick the weights up to shoulder level as you lie back.\n4. Retract your shoulder blades and plant your feet firmly on the floor.\n5. Press the dumbbells upward and slightly inward, keeping wrists stacked over elbows.\n6. Lower with control until dumbbells reach upper chest height, feeling a full pectoral stretch.\n7. Repeat for desired reps.",
            ],
            [
                'title' => 'Flat Barbell Bench Press Mastery',
                'description' => 'Master the chest press with proper scapular retraction, foot drive, and bar path control for maximum pectoral hypertrophy.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'chest',
                'focus_areas' => ['Chest', 'Triceps', 'Front Delt'],
                'equipment' => 'Barbell',
                'how_to_do' => "1. Lie flat on the bench with eyes directly under the racked bar.\n2. Grip the bar slightly wider than shoulder-width.\n3. Unrack with elbows locked and lower bar smoothly to mid-chest.\n4. Press powerfully upward until arms lock out.",
            ],
            [
                'title' => 'Lat Pulldown & Scapular Control',
                'description' => 'Develop a wide V-taper back posture. Learn how to initiate with lat depression rather than pulling with arms.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'back',
                'focus_areas' => ['Back', 'Biceps', 'Rear Delt'],
                'equipment' => 'Cable',
                'how_to_do' => "1. Sit facing the lat pulldown machine and secure thighs under the pad.\n2. Grasp the wide bar with an overhand grip.\n3. Pull the bar down to collarbone level while arching chest upward.\n4. Squeeze shoulder blades at bottom, then return under slow control.",
            ],
            [
                'title' => 'Bent-Over Barbell Row Form Guide',
                'description' => 'Build upper back thickness and posterior chain endurance. Maintain a 45-degree torso angle and drive elbows backwards.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'back',
                'focus_areas' => ['Back', 'Rhomboids', 'Lats'],
                'equipment' => 'Barbell',
                'how_to_do' => "1. Stand with feet hip-width apart holding a barbell.\n2. Hinge at hips until torso is roughly 45 degrees to floor.\n3. Pull the bar towards your lower ribcage, driving elbows toward ceiling.\n4. Lower under control and maintain a neutral spine throughout.",
            ],
            [
                'title' => 'Barbell Back Squat Depth & Mobility',
                'description' => 'Achieve full depth safely. Learn proper stance width, hip hinge mechanics, core bracing, and knee tracking over toes.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'legs',
                'focus_areas' => ['Quadriceps', 'Glutes', 'Hamstrings'],
                'equipment' => 'Barbell',
                'how_to_do' => "1. Rest barbell securely across upper traps.\n2. Stand with feet shoulder-width apart, toes turned slightly out.\n3. Inhale, brace core, and bend knees/hips down until thighs are parallel to ground.\n4. Drive through the mid-foot to stand back up.",
            ],
            [
                'title' => 'Romanian Deadlift Hip Hinge Guide',
                'description' => 'Isolate hamstrings and glutes with zero lower back strain. Master the hip hinge pattern with light to moderate weights.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1434682881908-b43d0467b798?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'legs',
                'focus_areas' => ['Hamstrings', 'Glutes', 'Lower Back'],
                'equipment' => 'Dumbbell',
                'how_to_do' => "1. Hold dumbbells in front of thighs with slight bend in knees.\n2. Push hips back as far as possible while keeping spine flat.\n3. Lower dumbbells along shins until hamstring stretch is felt.\n4. Drive hips forward to return to standing position.",
            ],
            [
                'title' => 'Dumbbell Shoulder Press Alignment',
                'description' => 'Sculpt broad 3D deltoids. Maintain vertical forearms and prevent excessive lower back arching.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackSeeTheWorld.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'shoulders',
                'focus_areas' => ['Shoulder', 'Triceps', 'Traps'],
                'equipment' => 'Dumbbell',
                'how_to_do' => "1. Sit or stand holding dumbbells at shoulder level with palms forward.\n2. Press dumbbells overhead in an arc until arms are extended.\n3. Avoid clanking weights at top.\n4. Lower smoothly back to ear height.",
            ],
            [
                'title' => 'Standing Dumbbell Bicep Curl Form',
                'description' => 'Isolate the biceps brachii. Eliminate momentum and keep elbows pinned at your sides for peak bicep peak contraction.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'arms',
                'focus_areas' => ['Biceps', 'Forearms'],
                'equipment' => 'Dumbbell',
                'how_to_do' => "1. Stand tall holding dumbbells with palms facing forward.\n2. Curl weights up while keeping upper arms fixed to sides.\n3. Squeeze biceps at peak contraction.\n4. Lower weights with a 3-second negative descent.",
            ],
            [
                'title' => 'Cable Triceps Rope Pushdown',
                'description' => 'Isolate lateral and medial heads of the triceps. Flare the rope outward at the bottom of the movement.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1530822847156-5df684ec5ee1?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'arms',
                'focus_areas' => ['Triceps', 'Forearms'],
                'equipment' => 'Cable',
                'how_to_do' => "1. Attach a rope to high cable pulley.\n2. Grasp rope, pin elbows at sides, and push downward until arms fully straighten.\n3. Flare rope ends outward for maximum triceps squeeze.\n4. Return under control to 90 degrees.",
            ],
            [
                'title' => 'Barbell Glute Hip Thrust Drive',
                'description' => 'The premier glute hypertrophy movement. Tuck chin, drive through heels, and achieve full posterior pelvic tilt at the top.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WhatCarCanYouGetForAGrand.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'glutes',
                'focus_areas' => ['Glutes', 'Hamstrings', 'Core'],
                'equipment' => 'Barbell',
                'how_to_do' => "1. Position upper back against a bench with padded barbell across hips.\n2. Plant feet flat on floor hip-width apart.\n3. Drive through heels, extending hips upward until thighs and torso align.\n4. Squeeze glutes hard at peak for 1 second before lowering.",
            ],
            [
                'title' => 'Hanging Leg Raise & Core Bracing',
                'description' => 'Build rock-solid abdominal strength. Focus on posterior pelvic rotation rather than just lifting the legs.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'core',
                'focus_areas' => ['Core', 'Abs', 'Hip Flexors'],
                'equipment' => 'Bodyweight',
                'how_to_do' => "1. Hang from a pull-up bar with an overhand grip.\n2. Brace abdominal wall and raise knees or straight legs toward chest.\n3. Roll pelvis upward at top to contract abs.\n4. Lower legs slowly without swinging.",
            ],
        ];

        foreach ($tutorials as $tutorial) {
            TutorialVideo::create($tutorial);
        }
    }
}
