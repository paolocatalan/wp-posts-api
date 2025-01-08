<?php

namespace App\Http\Controllers;

use App\Http\Requests\ViewPostRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index(ViewPostRequest $request): JsonResponse {
        $posts =  DB::connection('wordpress')->table('wp_posts');

        // conditional querying
        $posts->when($request->has('post_id'), function (Builder $query) use ($request) {
            $query->where('ID', $request->input('post_id'));
        });

        $posts->when($request->has('post_name'), function (Builder $query) use ($request) {
            $query->where('post_name', $request->input('post_name'));
        });

        $order = ($request->has('order')) ? $request->input('order') : 'DESC';
        $posts->when($request->has('orderby'),
            function (Builder $query) use ($request, $order) {
                $query->orderBy($request->input('orderby'), $order);
            },
            function (Builder $query) use ($order) {
                $query->orderBy('post_date', $order);
            }
        );

        $posts->when($request->has('post_type'),
            function (Builder $query) use ($request) {
                $query->where('post_type', $request->input('post_type'));
            },
            function (Builder $query) {
                $query->where('post_type', 'post');
            }
        );

        $posts->when($request->has('post_status'),
            function (Builder $query) use ($request) {
                $query->where('post_status', $request->input('post_status'));
            },
            function (Builder $query) {
                $query->where('post_status', 'publish');
            }
        );

        $posts->when($request->has('post_children'), function (Builder $query) use ($request) {
            if (is_numeric($request->input('post_children'))) {
                $postId = $request->input('post_children');
            } else {
                $post = DB::connection('wordpress')->table('wp_posts')->where('post_name', $request->input('post_children'))->firstOrFail();
                $postId = $post->ID;
            }

            $query->where('post_parent', $postId);
        });

        $posts->when($request->has('post_parent'), function (Builder $query) use ($request) {

            $wpPost = DB::connection('wordpress')->table('wp_posts');

            if (is_numeric($request->input('post_parent'))) {
                $postId = $request->input('post_parent');
            } else {
                $post = $wpPost->where('post_name', $request->input('post_parent'))->firstOrFail();
                $postId = $post->ID;
            }

            $postChild = $wpPost->where('ID', $postId)->firstOrFail();

            $query->where('ID', $postChild->post_parent);
        });

        $postsCount = count($posts->get());

        $posts->when($request->has('posts_per_page'), function (Builder $query) use ($request) {
            $pageSize = (int) $request->input('posts_per_page');
            $page = (int) $request->input('current_page');

            $offset = $pageSize * ($page - 1);

            $query->limit($pageSize);
            $query->offset($offset);
        });

        $posts->when($request->has('numberposts'), function (Builder $query) use ($request) {
            $query->limit($request->input('numberposts'));
        });

        // cache the results for each requested filtered combination
        $data = Cache::remember('posts_' . md5(json_encode($request->all())), 3600, function() use ($posts) {
            return $posts->get();
        });

        // No results found
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'No posts available for that criteria.'
            ]);
        }

        // pagination
        $pageSize = ($request->has('posts_per_page')) ? (int) $request->input('posts_per_page') : 1;
        if ($request->has('posts_per_page')) {
            $totalRecords = $postsCount;
            $totalPages = ceil($totalRecords/$pageSize);
        } else {
            $totalRecords = ($request->has('numberposts')) ? $request->input('numberposts') : $postsCount;
            $totalPages = 1;
        }
        $currentPage = ($request->has('current_page')) ? $request->input('current_page') : 1;

        return response()->json([
            'data' => $data,
            'pagination' => [
                'total_records' => (string) $totalRecords,
                'total_pages' => (string) $totalPages,
                'current_page' => (string) $currentPage
            ]
        ], 200);
    }
}
