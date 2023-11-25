<?php

class PostController extends WP_REST_Controller
{
    private $nameSpace = API_NAME . '/v1';
    public function registerRoutes()
    {
        register_rest_route($this->nameSpace, 'category', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'getCategory')
            ),
        ));
        register_rest_route($this->nameSpace, 'sub-category', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'getSubCategory')
            ),
        ));
        register_rest_route($this->nameSpace, 'posts-category/(?P<category>[a-zA-Z0-9-_]+)', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'getPostCategory')
            ),
        ));
        register_rest_route($this->nameSpace, 'post/(?P<post_slug>[a-zA-Z0-9-_]+)', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'getPost')
            ),
        ));
        register_rest_route($this->nameSpace, 'search', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'search')
            ),
        ));
        register_rest_route($this->nameSpace, 'send', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'send')
            ),
        ));
    }
    // public function send()
    // {

    //     $data = json_decode(file_get_contents('php://input'), true);

    //     $emailForm = (isset($data['to'])) ? $data['to'] : "";
    //     if (!filter_var($emailForm, FILTER_VALIDATE_EMAIL)) {
    //         return new WP_Error('Mail contact', __('Invalid email format'), array('status' => 500));
    //     }
    //     $emailTo = get_option('mail_from_name', true);
    //     $fullname = (isset($data['fullname'])) ? $data['fullname'] : "";
    //     $phone = (isset($data['phone'])) ? $data['phone'] : "";
    //     $company = (isset($data['company'])) ? $data['company'] : "";
    //     $content = (isset($data['content'])) ? $data['content'] : "";
    //     $subject = 'Liên hệ được gửi tử email: ' . $emailForm;
    //     $message = "<p>Fullname: $fullname</p>
    //     <p>Phone: $phone</p>
    //     <p>Company: $company</p>
    //     <p>Content: $content</p>";


    //     // Set SMTPDebug to true
    //     //$phpmailer->SMTPDebug = true;

    //     // Start output buffering to grab smtp debugging output
    //     //ob_start();

    //     // Send the test mail
    //     $result = wp_mail($emailTo, $subject, $message);
    //     $results = [];

    //     $results['code'] = 'success';
    //     $results['data'] = new stdClass;

    //     return new WP_REST_Response($results, 200);
    // }
    public function getCategory($request)
    {
        $result = [];

        $categories = get_terms([
            'taxonomy' => 'category',
            'parent' => 0,
        ]);
        return new WP_REST_Response($categories, 200);
    }
    public function getSubCategory($request)
    {

        $subCategories = get_terms([
            'taxonomy' => 'category',
            'childless' => true
        ]);
        return new WP_REST_Response($subCategories, 200);
    }
    public function getPost($request)
    {



        $results = [];
        $args = array(
            'post_type' => POST_TYPE,
            'post_status' => array('publish'),
            'name' => $request['post_slug']

        );
        $posts = new WP_Query($args);


        if ($posts->have_posts()) {
            $results['code'] = 'success';
            while ($posts->have_posts()) {
                $posts->the_post();
                $getTitle =  get_the_title();
                $listImage = get_post_meta(get_the_ID(), KEY_LIST_IMAGES . '_list', true);

                //Get content without caption
                $results['data'] = [
                    'title' => $getTitle,
                    'slug' => get_post_field('post_name', get_the_ID()),
                    'content' => get_the_content() ,
                    'short_description' =>  get_post_meta(get_the_ID(), KEY_SUMMARY , true),
                    'thumbnail' => has_post_thumbnail() ? get_the_post_thumbnail_url() : '',
                    'date' => get_the_date('Y/m/d'),
                ];

                $images = (is_array($listImage)) ? array_values($listImage) : [];
                $results['data']['images'] = $images;
            }

            wp_reset_postdata();
        } else {
            return new WP_Error('no_posts', __('No post found'), array('status' => 404));
        }
        return new WP_REST_Response($results, 200);
    }
    public function getPostCategory($request)
    {

        $results = [];


        $queryParams = $request->get_query_params();
        //Pagination param
        $page = 1;
        $postPerPage = (int)get_option('posts_per_page');
        if (isset($queryParams['page']) && $queryParams['page'] > 1) {
            $page = (int)$queryParams['page'];
        }
        //Get Post of category
        $args = array(
            'post_type' => POST_TYPE,
            'post_status' => array('publish'),
            'order' => 'DESC',
            'category_name' => $request['category'],
            'posts_per_page' => $postPerPage,
            'paged' => $page,
        );


        //Get data for glossary
        $posts = new WP_Query($args);
        if ($posts->have_posts()) {

            $results['code'] = 'success';
            $key = 0;
            // Set default data null
            while ($posts->have_posts()) {
                $posts->the_post();
                $getTitle =  get_the_title();
                $category_detail = get_the_category(get_the_ID()); //$post->ID
                //Get content without caption
                $results['data'][$key] = [
                    'title' => $getTitle,
                    'slug' => get_post_field('post_name', get_the_ID()),
                    'content' => get_the_content() ,
                    'short_description' =>  get_post_meta(get_the_ID(), KEY_SUMMARY , true),
                    'link_video' =>  get_post_meta(get_the_ID(), KEY_TEMPLATE_SERVICE . '_link', true),
                    'thumbnail' => has_post_thumbnail() ? get_the_post_thumbnail_url() : '',
                    'slug_category' => (!empty($category_detail)) ? $category_detail[0]->slug : "",
                    'date' => get_the_date('Y/m/d')
                ];

                $key++;
            }
            //Pagination data
            $results['pagination'] = [
                'current_page' => $page,
                'total' => (int)$posts->found_posts,
                'post_per_page' => $postPerPage,
            ];
            wp_reset_postdata();
            return new WP_REST_Response($results, 200);
        } else {
            return new WP_Error('no_posts', __('No post found'), array('status' => 404));
        }
    }

    public function search($request)
    {

        $results = [];
        $search = (isset($_GET['s'])) ? $_GET['s'] : "";

        $queryParams = $request->get_query_params();
        //Pagination param
        $page = 1;
        $postPerPage = (int)get_option('posts_per_page');
        if (isset($queryParams['page']) && $queryParams['page'] > 1) {
            $page = (int)$queryParams['page'];
        }

        //Get Post of category
        $args = array(
            'post_type' => POST_TYPE,
            'post_status' => array('publish'),
            'order' => 'DESC',
            's' => $search,
            'posts_per_page' => $postPerPage,
            'paged' => $page,
        );


        //Get data for glossary
        $posts = new WP_Query($args);

        if ($posts->have_posts()) {

            $results['code'] = 'success';
            $key = 0;
            // Set default data null
            while ($posts->have_posts()) {
                $posts->the_post();
                $getTitle =  get_the_title();
                $category_detail = get_the_category(get_the_ID()); //$post->ID


                //Get content without caption
                $results['data'][$key] = [
                    'title' => $getTitle,
                    'slug' => get_post_field('post_name', get_the_ID()),
                    'content' => get_the_content() ,
                    'short_description' =>  get_post_meta(get_the_ID(), KEY_SUMMARY , true),
                    'link_video' =>  get_post_meta(get_the_ID(), KEY_TEMPLATE_SERVICE . '_link', true),
                    'thumbnail' => has_post_thumbnail() ? get_the_post_thumbnail_url() : '',
                    'slug_category' => (!empty($category_detail)) ? $category_detail[0]->slug : "",
                    'date' => get_the_date('Y/m/d')

                ];

                $key++;
            }
            //Pagination data
            $results['pagination'] = [
                'current_page' => $page,
                'total' => (int)$posts->found_posts,
                'post_per_page' => $postPerPage,
            ];
            wp_reset_postdata();

            return new WP_REST_Response($results, 200);
        } else {
            return new WP_Error('no_posts', __('No post found'), array('status' => 404));
        }
    }
}

add_action('rest_api_init', function () {
    $shareController = new PostController();
    $shareController->registerRoutes();
});
