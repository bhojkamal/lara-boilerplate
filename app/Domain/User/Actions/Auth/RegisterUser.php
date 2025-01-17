<?php

namespace App\Domain\User\Actions\Auth;

use App\Domain\Team\Models\Team;
use App\Domain\User\Models\User;
use App\Domain\User\ValidationRules\PasswordValidationRules;
use App\Domain\User\ValueObjects\FullName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

/**
 * Class RegisterUser
 */
class RegisterUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param array<string, string> $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => $this->passwordRules(),
            'terms'    => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            return tap(
                User::create([
                    'full_name' => FullName::fromString($input['name']),
                    'email'     => $input['email'],
                    'username'  => $input['email'],
                    'password'  => Hash::make($input['password']),
                ]), function (User $user) {
                $this->createTeam($user);
            }
            );
        });
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(
            Team::forceCreate([
                'user_id'       => $user->id,
                'name'          => explode(' ', $user->name, 2)[0]."'s Team",
                'personal_team' => true,
            ])
        );
    }
}
