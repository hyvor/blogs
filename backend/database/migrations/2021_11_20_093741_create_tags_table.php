<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = <<<SQL
            CREATE TABLE tags (
                id serial PRIMARY KEY,
                created_at timestamp DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp DEFAULT CURRENT_TIMESTAMP,
                
                blog_id bigint NOT NULL,
                
                slug citext NOT NULL,
                
                posts_count integer DEFAULT 0,
                
                code_head text NULL,
                code_foot text NULL,
                
                is_private boolean DEFAULT false,
                
                UNIQUE (blog_id, slug)
            );
            CREATE INDEX tags_blog_id_index ON tags (blog_id);
        SQL;

        DB::unprepared($query);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}
