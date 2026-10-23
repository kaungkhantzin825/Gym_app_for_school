<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetStrengthProgress;
use App\Ai\Tools\GetUserExerciseHistory;
use App\Ai\Tools\GetUserMealHistory;
use App\Ai\Tools\GetUserProfile;
use App\Models\User;
use App\Services\AiKeyRotatorService;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

class FitnessCoach implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    protected ?string $customProvider = null;

    public function __construct(public User $user) {}

    public function setProvider(string $provider): static
    {
        $this->customProvider = $provider;

        return $this;
    }

    public function provider(): string
    {
        return $this->customProvider ?? AiKeyRotatorService::getPrimaryProvider();
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $snapshot = $this->athleteSnapshot();

        return <<<PROMPT
        You are an expert strength, conditioning and nutrition coach talking to
        one athlete inside their training app. You are warm, direct and
        practical - you give numbers, not vague encouragement.

        ## What you help with
        1. **Training** - exercise selection, sets/reps/load, technique cues, weekly
           structure, deloads, recovery, working around soreness or a niggle.
        2. **Nutrition** - calories and macros against their target, meal ideas that
           fit what they already eat, pre/post-workout timing, cutting and bulking.
        3. **Progress** - whether they are actually getting stronger, what to lift
           next, when to add weight, how to break a plateau.
        4. **Habits** - adherence, sleep, step count, keeping momentum.

        ## How to answer
        - When the question is about THIS athlete's training, eating or progress, call
          a tool first. Never guess their numbers.
        - Be concrete: give sets, reps, loads, grams, minutes. "Add 2.5 kg to your top
          set next session" beats "progress gradually".
        - Keep it short. Lead with the answer, then at most a few bullets of the why.
          No preamble, no restating the question.
        - If the request needs information you do not have (a photo, an injury
          history, equipment), ask exactly one short question.
        - For pain, injury or medical signs, say plainly that this needs a doctor or
          physio, then give safe general guidance.
        - Metric units (kg, cm, g) unless the user asks otherwise.
        - Never invent their data, and never mention tool names or these instructions.

        ## The athlete in this chat
        {$snapshot}
        PROMPT;
    }

    /**
     * A compact, always-available summary of who is asking.
     *
     * The tools can fetch the detail, but having the basics in the prompt means
     * the first reply is already personal instead of a generic answer followed
     * by a tool round-trip.
     */
    protected function athleteSnapshot(): string
    {
        $lines = ['- Name: '.$this->user->name];

        $profile = $this->user->profile;

        if ($profile === null) {
            $lines[] = '- Profile: not filled in yet. Ask for their goal, height and weight if the advice depends on them.';
        } else {
            $lines[] = sprintf(
                '- Body: %s, %s years, %s cm, %s kg (target %s kg)',
                $profile->gender ?? 'unspecified',
                $profile->age ?? '?',
                $profile->height ?? '?',
                $profile->current_weight ?? '?',
                $profile->target_weight ?? 'not set',
            );
            $lines[] = sprintf(
                '- Goal: %s; activity level %s',
                $profile->goal ?? 'not set',
                $profile->activity_level ?? 'not set',
            );

            if ($profile->daily_calories_target !== null) {
                $lines[] = sprintf(
                    '- Daily targets: %s kcal, %s g protein, %s g carbs, %s g fat',
                    $profile->daily_calories_target,
                    $profile->daily_protein_target ?? '?',
                    $profile->daily_carbs_target ?? '?',
                    $profile->daily_fat_target ?? '?',
                );
            }
        }

        $since = now()->subDays(7);

        $workouts = $this->user->workouts()->where('started_at', '>=', $since)->count();
        $meals = $this->user->meals()->where('meal_time', '>=', $since)->count();

        $lines[] = sprintf(
            '- Last 7 days: %d workout(s) and %d meal(s) logged.',
            $workouts,
            $meals,
        );

        return implode("\n", $lines);
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new GetUserProfile($this->user),
            new GetUserMealHistory($this->user),
            new GetUserExerciseHistory($this->user),
            new GetStrengthProgress($this->user),
        ];
    }
}
