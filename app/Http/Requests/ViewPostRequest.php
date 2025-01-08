<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViewPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numberposts' => [Rule::prohibitedif(request()->hasany(['post_id', 'post_name', 'post_parent', 'posts_per_page'])), 'numeric', 'min:1'],
            'orderby' => [Rule::prohibitedif(request()->hasany(['post_id', 'post_name', 'post_parent'])), 'string', 'in:post_name,post_date'],
            'order' => [Rule::prohibitedif(request()->hasany(['post_id', 'post_name', 'post_parent'])), 'string', 'in:DESC,ASC'],
            'post_id' => [Rule::prohibitedIf(request()->has('post_name')), 'numeric'],
            'post_name' => [Rule::prohibitedIf(request()->has('post_id')), 'string'],
            'post_type' => ['string'],
            'post_status' => ['string', 'in:Publish,Pending,Draft'],
            'post_children' => [Rule::prohibitedIf(request()->hasAny(['post_id', 'post_name', 'post_parent']))],
            'post_parent' => [Rule::prohibitedIf(request()->hasAny(['post_id', 'post_name', 'post_children']))],
            'posts_per_page' => [Rule::prohibitedIf(request()->hasAny(['post_id', 'post_name', 'post_parent'])), 'numeric', 'min:1'],
            'current_page' => [Rule::requiredIf(request()->has('posts_per_page')), 'numeric', 'min:1']
        ];
    }

    public function messages(): array
    {
        return [
            'numberposts.prohibited' => "Please use only one of these parameters to set the number of posts: 'numberposts' or 'posts_per_page'.",
            'orderby.prohibited' => "The 'orderby' parameter cannot be used when requesting one of the parameters: 'post_id', 'post_name', 'post_parent'",
            'order.prohibited' => "The 'order' parameter cannot be used when requesting one of the parameters: 'post_id', 'post_name', 'post_parent'",
            'post_id.prohibited' => "You can only use one of the parameters: 'post_id' or 'post_name'",
            'post_name.prohibited' => "You can only use one of the parameters: 'post_id' or 'post_name'",
            'post_children.prohibited' => "The 'post_children' parameter cannot be used when requesting one of the parameters: 'post_id', 'post_name', 'post_parent'",
            'post_parent.prohibited' => "The 'post_parent' parameter cannot be used when requesting one of the parameters: 'post_id', 'post_name', 'post_children'",
            'posts_per_page.prohibited' => "The 'order' parameter cannot be used when requesting single post.",
        ];
    }
}
