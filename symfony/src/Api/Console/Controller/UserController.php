<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Input\Blog\User\CheckUserSlugAvailableInput;
use App\Api\Console\Input\Blog\User\CreateGuestUserInput;
use App\Api\Console\Input\Blog\User\CreateUserInput;
use App\Api\Console\Input\Blog\User\CreateUserVariantInput;
use App\Api\Console\Input\Blog\User\DeleteUserVariantInput;
use App\Api\Console\Input\Blog\User\GetUsersInput;
use App\Api\Console\Input\Blog\User\SearchUsersInput;
use App\Api\Console\Input\Blog\User\UpdateUserInput;
use App\Api\Console\Input\Blog\User\UpdateUserVariantInput;
use App\Api\Console\Object\UserObjectFactory;
use App\Api\Console\Object\UserVariantObjectFactory;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Service\Billing\UsageService;
use App\Service\Language\LanguageService;
use App\Service\User\UserService;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization\VerifyMember;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class UserController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private UserService $userService,
        private LanguageService $languageService,
        private UserObjectFactory $userObjectFactory,
        private UserVariantObjectFactory $userVariantObjectFactory,
        private UsageService $usageService,
        private CommsInterface $comms,
        private InternalConfig $internalConfig,
    ) {}

    #[Route('/users', methods: ['GET'])]
    public function getUsers(
        #[MapQueryString] GetUsersInput $input = new GetUsersInput(),
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $users = $this->userService->getUsers($blog, $input->limit, $input->offset);

        return new JsonResponse(array_map(
            fn($user) => $this->userObjectFactory->create($user, $blog),
            $users,
        ));
    }

    #[Route('/users/search', methods: ['GET'])]
    public function searchUsers(
        #[MapQueryString] SearchUsersInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $users = $this->userService->searchUsers($blog, $input->search, limit: 10);

        return new JsonResponse(array_map(
            fn($user) => $this->userObjectFactory->create($user, $blog),
            $users,
        ));
    }

    #[Route('/user', methods: ['POST'])]
    public function createUser(
        #[MapRequestPayload] CreateUserInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->usageService->usersLimitReached($blog)) {
            throw new UnprocessableEntityHttpException('Max users limit exceeded. Please upgrade your plan');
        }

        if ($this->userService->getUserByHyvorUserId($blog, $input->hyvor_user_id) !== null) {
            throw new UnprocessableEntityHttpException('User is already added to the blog');
        }

        if ($input->role === UserRole::OWNER) {
            throw new UnprocessableEntityHttpException('Owners cannot be created. Use ownership transferring');
        }

        $organizationId = $blog->getOrganizationId();
        assert($organizationId !== null);

        if ($this->internalConfig->getDeployment()->isCloud()) {
            try {
                $verification = $this->comms->send(
                    new VerifyMember($organizationId, $input->hyvor_user_id),
                );
            } catch (CommsApiFailedException) {
                throw new UnprocessableEntityHttpException('Unable to verify the user. Please try again later.');
            }

            if (!$verification->isMember()) {
                throw new UnprocessableEntityHttpException('Unable to find the user in the organization');
            }
        }

        $user = $this->userService->createUserFromHyvorUser($blog, $input->hyvor_user_id, $input->role);

        return new JsonResponse($this->userObjectFactory->create($user, $blog));
    }

    #[Route('/user/guest', methods: ['POST'])]
    public function createGuestUser(
        #[MapRequestPayload] CreateGuestUserInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->usageService->usersLimitReached($blog)) {
            throw new UnprocessableEntityHttpException('Max users limit exceeded. Please upgrade your plan');
        }

        $user = $this->userService->createGuestUser($blog, $input->name);

        return new JsonResponse($this->userObjectFactory->create($user, $blog));
    }

    #[Route('/user/{id}', methods: ['PATCH'])]
    public function updateUser(
        #[MapBlogEntity] User $user,
        #[MapRequestPayload] UpdateUserInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($input->role !== null) {
            if ($input->role === UserRole::OWNER) {
                throw new UnprocessableEntityHttpException(
                    'You cannot update the role to owner. Use transferring instead',
                );
            }
            if ($user->getRole() === UserRole::OWNER) {
                throw new UnprocessableEntityHttpException(
                    'You cannot update the role of the owner. Use transferring instead',
                );
            }
        }

        if ($input->status !== null && $user->getRole() === UserRole::OWNER) {
            throw new UnprocessableEntityHttpException('You cannot update the status of the owner');
        }

        if (
            $input->slug !== null &&
            ($existing = $this->userService->getUserBySlug($blog, $input->slug)) !== null &&
            $existing->getId() !== $user->getId()
        ) {
            throw new UnprocessableEntityHttpException('Slug already taken');
        }

        $user = $this->userService->updateUser($user, (array) $input);

        return new JsonResponse($this->userObjectFactory->create($user, $blog));
    }

    #[Route('/user/{id}', methods: ['DELETE'])]
    public function deleteUser(#[MapBlogEntity] User $user): JsonResponse
    {
        if ($user->getRole() === UserRole::OWNER) {
            throw new UnprocessableEntityHttpException('Cannot delete the owner');
        }

        $this->userService->deleteUser($user);

        return new JsonResponse();
    }

    #[Route('/user/{id}/slug-available', methods: ['GET'])]
    public function checkSlugAvailability(
        #[MapBlogEntity] User $user,
        #[MapQueryString] CheckUserSlugAvailableInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $slugUser = $this->userService->getUserBySlug($blog, $input->slug);

        return new JsonResponse([
            'available' => $slugUser === null || $slugUser->getId() === $user->getId(),
        ]);
    }

    #[Route('/user/{id}/variant', methods: ['POST'])]
    public function createVariant(
        #[MapBlogEntity] User $user,
        #[MapRequestPayload] CreateUserVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        $variant = $this->userService->createUserVariant($user, $language);

        return new JsonResponse($this->userVariantObjectFactory->create($variant, $user, $blog));
    }

    #[Route('/user/{id}/variant', methods: ['PATCH'])]
    public function updateVariant(
        #[MapBlogEntity] User $user,
        #[MapRequestPayload] UpdateUserVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        $variant = $this->userService->getUserVariant($user, $language);
        if ($variant === null) {
            throw new NotFoundHttpException('Variant not found');
        }

        $variant = $this->userService->updateUserVariant($variant, (array) $input);

        return new JsonResponse($this->userVariantObjectFactory->create($variant, $user, $blog));
    }

    #[Route('/user/{id}/variant', methods: ['DELETE'])]
    public function deleteVariant(
        #[MapBlogEntity] User $user,
        #[MapRequestPayload] DeleteUserVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        if ($language->isPrimary()) {
            throw new UnprocessableEntityHttpException(
                'Primary language variant cannot be deleted. Delete the user instead',
            );
        }

        $variant = $this->userService->getUserVariant($user, $language);
        if ($variant === null) {
            throw new NotFoundHttpException('Variant not found');
        }

        $this->userService->deleteUserVariant($variant);

        return new JsonResponse();
    }
}
