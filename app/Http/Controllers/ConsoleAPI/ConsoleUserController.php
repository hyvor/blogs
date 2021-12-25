<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\Request;

class ConsoleUserController extends Controller {

    private $userRepo;

    public function __construct(UserRepositoryInterface $userRepo) {
        $this->userRepo = $userRepo;
    }

    public function getBlogs(Request $request) {
        return $this->userRepo->getBlogs(1, 'hyvor');
    }

    public function createBlog(Request $request) {
        $request->validate([
            'subdomain' => 'required|unique:blogs',
            'name' => 'required|max:255',
        ]);

        return $this->userRepo->createBlog(
            1,
            $request->input('subdomain'),
            $request->input('name')
        );
    }

    public function changeSort(Request $request) {
        $request->validate([
            'blogs' => 'required|array'
        ]);
        $this->userRepo->changeSorts(1, 'hyvor', $request->input('blogs'));
    }

}