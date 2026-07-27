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
                'title' => 'Flat Barbell Bench Press Mastery',
                'description' => 'Master the chest press with proper scapular retraction, foot drive, and bar path control for maximum pectoral hypertrophy.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'chest',
            ],
            [
                'title' => 'Incline Dumbbell Press Technique',
                'description' => 'Target the upper clavicular head of the pectoralis major. Focus on deep stretch at the bottom and hard contraction at peak.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'chest',
            ],
            [
                'title' => 'Lat Pulldown & Scapular Control',
                'description' => 'Develop a wide V-taper back posture. Learn how to initiate with lat depression rather than pulling with arms.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'back',
            ],
            [
                'title' => 'Bent-Over Barbell Row Form Guide',
                'description' => 'Build upper back thickness and posterior chain endurance. Maintain a 45-degree torso angle and drive elbows backwards.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'back',
            ],
            [
                'title' => 'Barbell Back Squat Depth & Mobility',
                'description' => 'Achieve full depth safely. Learn proper stance width, hip hinge mechanics, core bracing, and knee tracking over toes.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'legs',
            ],
            [
                'title' => 'Romanian Deadlift Hip Hinge Guide',
                'description' => 'Isolate hamstrings and glutes with zero lower back strain. Master the hip hinge pattern with light to moderate weights.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1434682881908-b43d0467b798?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'legs',
            ],
            [
                'title' => 'Overhead Military Press Form',
                'description' => 'Strict overhead press execution. Squeeze glutes and brace core to prevent hyperextension of the lumbar spine.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackAmazingArgument.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'shoulders',
            ],
            [
                'title' => 'Dumbbell Lateral Raise Isolation',
                'description' => 'Isolate the lateral head of the shoulder for 3D width. Lead with elbows and control the lowering phase.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'shoulders',
            ],
            [
                'title' => 'Biceps Incline Dumbbell Curl',
                'description' => 'Maximize biceps long-head stretch on an incline bench. Keep shoulders back and minimize momentum.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'biceps',
            ],
            [
                'title' => 'Core Hanging Leg Raise & Ab Bracing',
                'description' => 'Strengthen rectus abdominis and hip flexors. Avoid swinging and flex hips to pull pelvis upward at the top.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WhatCarCanYouGetForAGrand.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=600',
                'gender_target' => 'all',
                'muscle_group' => 'core',
            ],
        ];

        foreach ($tutorials as $t) {
            TutorialVideo::create($t);
        }
    }
}
