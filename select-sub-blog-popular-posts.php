<?php
/*
Plugin Name: Popular Sub Blog Posts Selector
Plugin URI:
Description: Popular Sub Blog Posts Selector
Version: 1
Author: Jason Rowe
Author URI: https://www.idealwebdesign.net
License: GPL2
*/


namespace VuePosts;
defined('ABSPATH') or die('No direct script access!');

function get_saved_popular_posts()
{
    $options = get_option('saved_popular_posts');

    if ($options) {
        return $options;
    } else {
        return false;
    }

}


function save_popular_posts()
{

    if (!isset($_POST['site_nonce']) || !wp_verify_nonce($_POST['site_nonce'], 'site_nonce')) {

        die('failed');
    }


    $save_popular_posts_array['firstpost'] = json_decode(stripslashes($_POST['firstpost']));
    $save_popular_posts_array['secondpost'] = json_decode(stripslashes($_POST['secondpost']));
    $save_popular_posts_array['thirdpost'] = json_decode(stripslashes($_POST['thirdpost']));
    $save_popular_posts_array['fourthpost'] = json_decode(stripslashes($_POST['fourthpost']));
    $save_popular_posts_array['fifthpost'] = json_decode(stripslashes($_POST['fifthpost']));
    $save_popular_posts_array['sixthpost'] = json_decode(stripslashes($_POST['sixthpost']));
    update_option('saved_popular_posts', $save_popular_posts_array);
    die('success');

}

function get_table_data()
{
    global $post;

    $your_list_of_blog_ids = get_sites();
    $tablerows = [];
    $authorsarray = [];
    foreach ($your_list_of_blog_ids as $blog_id) {
        if($blog_id->blog_id!=1) { // only proceed if not the main blog
            switch_to_blog($blog_id->blog_id); //switched to blog

            $args = array('post_type' => 'post','order' => 'DESC',
                'post_status' => 'publish');
            $all_posts = get_posts($args);
            $tablerow = [];
            if (count($all_posts) > 0) {
                foreach ($all_posts as $post) {

                    setup_postdata($post);

                    $tablerow['blogid'] = $blog_id->blog_id;

                    $author_id = get_post_field('post_author', get_the_ID());
                    $tablerow['authorname'] = get_display_name_by_id(($author_id));
                    array_push($authorsarray, get_display_name_by_id(($author_id)));
                    $tablerow['postid'] = get_the_ID();
                    $tablerow['posttitle'] = get_the_title();
                    array_push($tablerows, $tablerow);
                    wp_reset_postdata();
                }
            }
            restore_current_blog();
        }
    }
    //complete
    $finalauthorarray = array_unique($authorsarray);
    $finalauthorarray = array_values($finalauthorarray);

    $returneddata['tabledata'] = $tablerows;
    $returneddata['authors'] = $finalauthorarray;

    return $returneddata;
//var_dump($returneddata);


}

function get_display_name_by_id($user_id)
{
    global $wpdb;

    if (!$user = $wpdb->get_row($wpdb->prepare(
        "SELECT `display_name` FROM $wpdb->users WHERE `ID` = %d", $user_id
    ))
    )
        return false;

    return $user->display_name;
}

function get_userinfo_by_id($user_id)
{
    global $wpdb;

    if (!$user = $wpdb->get_row($wpdb->prepare(
        "SELECT `display_name`,`user_login` FROM $wpdb->users WHERE `ID` = %d", $user_id
    ))
    )
        return false;

    return $user;
}

function get_username_by_id($user_id)
{
    global $wpdb;

    if (!$user = $wpdb->get_row($wpdb->prepare(
        "SELECT `user_login` FROM $wpdb->users WHERE `ID` = %d", $user_id
    ))
    )
        return false;

    return $user->user_login;
}

add_action('admin_menu', function () {
    add_options_page('Popular Sub Blog Posts', 'Popular Sub Blog Posts', 'edit_pages', 'subblogposts', __NAMESPACE__ . '\\createAdminPage', 'dashicons-clipboard', 90);
});
function createAdminPage()
{

    ?>
    <div id="app" style="padding:20px;" v-loading="loading">


    </div>


    <!-- import CSS -->
    <link rel="stylesheet" href="https://unpkg.com/element-ui@2.3.2/lib/theme-chalk/index.css">
    <!-- import JavaScript -->

    <script src="https://unpkg.com/vue/dist/vue.js"></script>
    <script src="https://unpkg.com/element-ui@2.3.2/lib/index.js"></script>
    <script src="https://unpkg.com/element-ui/lib/umd/locale/en.js"></script>

    <script>
        ELEMENT.locale(ELEMENT.lang.en)

    </script>

    <style>
        .el-row {
            margin-bottom: 20px;

        &
        :last-child {
            margin-bottom: 0;
        }

        }
        .el-col {
            border-radius: 4px;
        }

        .bg-purple-dark {
            background: #99a9bf;
        }

        .bg-purple {
            background: #d3dce6;
        }

        .bg-purple-light {
            background: #e5e9f2;
        }

        .grid-content {
            border-radius: 4px;
            min-height: 36px;
        }

        .row-bg {
            padding: 10px 0;
            background-color: #f9fafc;
        }

        .el-button + .el-button {
            margin-left: 0px;
        }

        .el-button--small {
            padding: 5px;
        }

        .el-button--danger {
            padding: 5px;
        }

        .el-notification.left {
            top: 100px !important;
            left: 180px;
        }
    </style>
    <template id="apptemplate">
        <div>
            <el-row>

                <el-col :span="24">
                    <h2 style="text-align:center;">Choose which posts and the order they will appear within the most
                        popular sub blog posts section.</h2>
                    <p style="text-align:center;">
                        Click the down arrow next to the Author Name header to filter out authors to make finding posts
                        easier.
                    </p>
                    <hr>
                </el-col>
            </el-row>
            <el-row>

                <el-col :span="6">
                    <b>Choosen posts/order:</b>
                    <hr>
                    <div v-if="firstpost">

                        <p>

                            1st post : {{firstpost.posttitle}}<br>
                            <el-button type="danger" @click="firstpost=''">
                                Remove 1st
                            </el-button>
                        </p>
                    </div>
                    <div v-if="secondpost">
                        <p>
                            2nd post : {{secondpost.posttitle}}<br>
                            <el-button type="danger" @click="secondpost=''">
                                Remove 2nd
                            </el-button>
                        </p>
                    </div>

                    <div v-if="thirdpost">
                        <p>
                            3rd post : {{thirdpost.posttitle}}<br>
                            <el-button type="danger" @click="thirdpost=''">
                                Remove 3rd
                            </el-button>
                        </p>
                    </div>

                    <div v-if="fourthpost">
                        <p>
                            4th post : {{fourthpost.posttitle}}<br>
                            <el-button type="danger" @click="fourthpost=''">
                                Remove 4th
                            </el-button>
                        </p>
                    </div>
                    <div v-if="fifthpost">
                        <p>
                            5th post : {{fifthpost.posttitle}}<br>
                            <el-button type="danger" @click="fifthpost=''">
                                Remove 5th
                            </el-button>
                        </p>
                    </div>
                    <div v-if="sixthpost">
                        <p>
                            6th post : {{sixthpost.posttitle}}<br>
                            <el-button type="danger" @click="sixthpost=''">
                                Remove 6th
                            </el-button>
                        </p>
                    </div>

                    <hr>
                    <el-button type="primary" @click="saveposts" :disabled="!savedisabled">
                        Save
                    </el-button>
                    <el-button type="danger" @click="clearposts" :disabled="!cleardisabled">
                        Clear
                    </el-button>

                </el-col>

                <el-col :span="18">
                    <el-table :data="tableData" style="width: 100%">
                        <el-table-column prop="blogid" label="Blog ID" width="90">
                        </el-table-column>
                        <el-table-column prop="authorname" :filters="finalauthornamefilter"
                                         :filter-method="filterauthorhandler" label="Author Name" width="150">
                        </el-table-column>
                        <el-table-column prop="postid" label="Post ID" width="90">
                        </el-table-column>
                        <el-table-column prop="posttitle" sortable label="Post Title" width=""
                        >
                        </el-table-column>


                        <el-table-column fixed="right" label="Sets as:" width="300">
                            <template slot-scope="scope">
                                <el-button @click="setasfirst(scope)" type="" size="small">1st</el-button>
                                <el-button @click="setassecond(scope)" type="" size="small">2nd</el-button>
                                <el-button @click="setasthird(scope)" type="" size="small">3rd</el-button>
                                <el-button @click="setasfourth(scope)" type="" size="small">4th</el-button>
                                <el-button @click="setasfifth(scope)" type="" size="small">5th</el-button>
                                <el-button @click="setassixth(scope)" type="" size="small">6th</el-button>

                            </template>
                        </el-table-column>
                    </el-table>
                </el-col>
            </el-row>


        </div>
    </template>
    <script>
        Vue.component('appwrapper', {
                template: '#apptemplate',
                computed: {
                    savedisabled: function () {
                        return this.firstpost && this.secondpost && this.thirdpost && this.fourthpost && this.fifthpost && this.sixthpost;
                    },
                    cleardisabled: function () {
                        return this.firstpost || this.secondpost || this.thirdpost || this.fourthpost || this.fifthpost || this.sixthpost;
                    },
                    finalauthornamefilter: function () {
                        var arrayofnames = [];
                        if (this.authorsnametofilter.length == 0) {
                            return {
                                text: 'none', value: 'none'
                            };

                        }
                        arrayofnames = this.authorsnametofilter.map(function (name) {
                            return {
                                text: name, value: name
                            }

                        });

                        //   console.log(arrayofnames);
                        return arrayofnames;
                    }

                },
                methods: {
                    clearposts: function () {
                        this.firstpost = '';
                        this.secondpost = '';
                        this.thirdpost = '';
                        this.fourthpost = '';
                        this.fifthpost = '';
                        this.sixthpost = '';
                    },
                    saveposts: function () {
                        //alert('Saving of posts complete.');

                        var loading = this.$loading({
                            lock: true,
                            text: 'Saving..',
                            spinner: 'el-icon-loading',
                            background: 'rgba(0, 0, 0, 0.9)'
                        });
                        var saveajaxdata = {

                            'action': 'save_popular_posts',
                            'firstpost': JSON.stringify(this.firstpost),
                            'secondpost': JSON.stringify(this.secondpost),
                            'thirdpost': JSON.stringify(this.thirdpost),
                            'fourthpost': JSON.stringify(this.fourthpost),
                            'fifthpost': JSON.stringify(this.fifthpost),
                            'sixthpost': JSON.stringify(this.sixthpost),
                            "site_nonce": site_variables.site_nonce
                        }
                        console.log(saveajaxdata);
                        var that = this;
                        jQuery.post(ajaxurl, saveajaxdata, function (response) {
                            loading.close();
                            if (response == 'success') {

                                var h = that.$createElement;
                                that.$notify({
                                    title: 'Status',
                                    message: h('i', {style: 'color: teal'}, 'Save complete.'),
                                    position: 'top-left'
                                });
                            } else {
                                console.log('save failed');
                                var h = that.$createElement;
                                that.$notify({
                                    title: 'Status',
                                    message: h('i', {style: 'color: red'}, 'Save failed.'),
                                    position: 'top-left'
                                });

                            }


                        });

                    },
                    filterauthorhandler: function (value, row, column) {
                        var property = column['property'];
                        return row[property] === value;
                    },
                    setasfirst: function (scope) {
                        this.firstpost = {
                            blogid: scope.row.blogid,
                            authorname: scope.row.authorname,
                            postid: scope.row.postid,
                            posttitle: scope.row.posttitle,

                        }

                    },
                    setassecond: function (scope) {
                        this.secondpost = {
                            blogid: scope.row.blogid,
                            authorname: scope.row.authorname,
                            postid: scope.row.postid,
                            posttitle: scope.row.posttitle,

                        }
                    },
                    setasthird: function (scope) {
                        this.thirdpost = {
                            blogid: scope.row.blogid,
                            authorname: scope.row.authorname,
                            postid: scope.row.postid,
                            posttitle: scope.row.posttitle,

                        }
                    },
                    setasfourth: function (scope) {
                        this.fourthpost = {
                            blogid: scope.row.blogid,
                            authorname: scope.row.authorname,
                            postid: scope.row.postid,
                            posttitle: scope.row.posttitle,

                        }
                    },
                    setasfifth: function (scope) {
                        this.fifthpost = {
                            blogid: scope.row.blogid,
                            authorname: scope.row.authorname,
                            postid: scope.row.postid,
                            posttitle: scope.row.posttitle,

                        }
                    },
                    setassixth: function (scope) {
                        this.sixthpost = {
                            blogid: scope.row.blogid,
                            authorname: scope.row.authorname,
                            postid: scope.row.postid,
                            posttitle: scope.row.posttitle,

                        }
                    },
                    handleClick: function (zip, address) {
                        console.log(zip + ' ' + address);
                    }

                },

                data: function () {
                    return {
                        <?php
                        $initial_options = get_saved_popular_posts();

                        ?>

                        <?php if($initial_options == false ):?>
                        firstpost: '',
                        secondpost: '',
                        thirdpost: '',
                        fourthpost: '',
                        fifthpost: '',
                        sixthpost: '',

                        <?php else: //initial defaults from returned options ?>
                        firstpost:<?php echo json_encode($initial_options['firstpost']); ?>,
                        secondpost:<?php echo json_encode($initial_options['secondpost']); ?>,
                        thirdpost:<?php echo json_encode($initial_options['thirdpost']); ?>,
                        fourthpost:<?php echo json_encode($initial_options['fourthpost']); ?>,
                        fifthpost:<?php echo json_encode($initial_options['fifthpost']); ?>,
                        sixthpost:<?php echo json_encode($initial_options['sixthpost']); ?>,
                        <?php endif; ?>
                        authorsnametofilter:<?php
                        $tabledata = get_table_data();
                        echo json_encode($tabledata['authors']);
                        ?>

                        ,
                        tableData: <?php

                        echo json_encode($tabledata['tabledata']);
                        ?>
                    }
                }
            }
        )


        new Vue({
            el: '#app',
            template: '<appwrapper></appwrapper>'

        });

    </script>
    <?php
}

function add_nonce_variable_in_javascript_to_admin_head_area()
{ ?>
    <script type="text/javascript">
        var site_variables = {
            'site_nonce':<?php echo json_encode(wp_create_nonce("site_nonce")); ?>
        };
    </script><?php
}

function my_fn_uninstall()
{
    delete_option('saved_popular_posts');
}


function popular_posts_shortcode()
{
    $saved_popular_posts = get_option('saved_popular_posts');

    if (!$saved_popular_posts) {
        return 'No posts to show';
    } else {
        $array_of_posts = [];
        array_push($array_of_posts, $saved_popular_posts['firstpost']);
        array_push($array_of_posts, $saved_popular_posts['secondpost']);
        array_push($array_of_posts, $saved_popular_posts['thirdpost']);
        array_push($array_of_posts, $saved_popular_posts['fourthpost']);
        array_push($array_of_posts, $saved_popular_posts['fifthpost']);
        array_push($array_of_posts, $saved_popular_posts['sixthpost']);
        $currentpost = 1;

        ob_start();
        foreach ($array_of_posts as $post) {

            if ($currentpost == 1 || $currentpost == 4) {
                echo '<div class="td-block-row">';
            }
            post_template($post);

            if ($currentpost == 3 || $currentpost == 6) {
                echo '</div>';
            }
            $currentpost++;
        }
        return ob_get_clean();
    }

}

function post_template($enteredpost)
{
//get post from subblog etc
    global $post;
    switch_to_blog($enteredpost->blogid); //switched to blog

    $post = get_post($enteredpost->postid); // really should only be one


    setup_postdata($post);
    $author = get_userinfo_by_id($post->post_author);
    //var_dump(parse_url(get_post_permalink($post))['path']);
    ?>

    <div class="td-block-span4">

        <div class="td_module_2 td_module_wrap td-animation-stack">
            <div class="td-module-image">
                <div class="td-module-thumb"><a href="<?php echo $post->guid; ?>" rel="bookmark"
                                                title="<?php echo get_the_title($post); ?>"><img width="324"
                                                                                                 height="160"
                                                                                                 class="entry-thumb td-animation-stack-type0-1"
                                                                                                 src="<?php echo get_the_post_thumbnail_url($post,'td_324x160'); ?>"
                                                                                                 srcset="<?php echo get_the_post_thumbnail_url($post,'td_324x160'); ?> 324w, <?php echo get_the_post_thumbnail_url($post,'td_533x261'); ?> 533w"
                                                                                                 sizes="(max-width: 324px) 100vw, 324px"
                                                                                                 alt="" title=""
                                                                                                 style=""></a></div>
            </div>
            <h3 class="entry-title td-module-title"><a href="<?php echo $post->guid; ?>" rel="bookmark"
                                                       title="<?php echo get_the_title($post); ?>"><?php echo get_the_title($post); ?></a>
            </h3>

            <div class="td-module-meta-info">
                <span class="td-post-author-name"><a
                            href="https://www.swedesinthestates.com<?php echo parse_url(get_post_permalink($post))['path']; ?>"><?php echo $author->display_name; ?></a> <span>-</span> </span>
                <span class="td-post-date"><time class="entry-date updated td-module-date"
                                                 datetime="<?php echo strtotime($post->post_date); ?>"><?php echo date('F n, Y', strtotime($post->post_date)) ?></time></span>
                <div class="td-module-comments"><a
                            href="<?php echo get_post_permalink($post); ?>/#respond"><?php echo $post->comment_count; ?></a>
                </div>
            </div>


            <!--   <div class="td-excerpt">
                <?php// echo $post->post_excerpt; ?></div> -->


        </div>


    </div><!-- ./td-block-span4 -->
    <?php wp_reset_postdata();
    // endforeach;
    restore_current_blog(); //switched back to main site

}

add_action('admin_head', __NAMESPACE__ . '\\add_nonce_variable_in_javascript_to_admin_head_area');
add_action('wp_ajax_save_popular_posts', __NAMESPACE__ . '\\save_popular_posts');
register_uninstall_hook(__FILE__, __NAMESPACE__ . '\\my_fn_uninstall');
add_shortcode('popular_posts', __NAMESPACE__ . '\\popular_posts_shortcode');

