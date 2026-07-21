<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetUserExerciseHistory;
use App\Ai\Tools\GetUserMealHistory;
use App\Ai\Tools\GetUserProfile;
use App\Models\User;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class FitnessCoach implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(public User $user) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
        You are a knowledgeable and encouraging AI fitness coach. You help users with:

        1. **Nutrition guidance** - Meal planning, macro tracking advice, food suggestions
        2. **Workout advice** - Exercise form tips, workout planning, recovery guidance
        3. **Goal setting** - Realistic goal setting based on their profile and progress
        4. **Motivation** - Encouraging progress, celebrating wins, providing accountability

        Guidelines:
        - Use the available tools to access the user's profile, meal history, and exercise history for personalized advice
        - Be encouraging but honest
        - Provide evidence-based fitness and nutrition information
        - Suggest consulting a doctor for medical concerns
        - Keep responses concise and actionable
        - Use metric units (kg, cm) unless the user specifies otherwise
        PROMPT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return \Laravel\Ai\Contracts\Tool[]
     */
    public function tools(): iterable
    {
        return [
            new GetUserProfile($this->user),
            new GetUserMealHistory($this->user),
            new GetUserExerciseHistory($this->user),
        ];
    }
}
