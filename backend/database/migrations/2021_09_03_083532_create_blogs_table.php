<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $query = <<<SQL
        
        DROP TYPE IF EXISTS blog_type;
        CREATE TYPE blog_type AS ENUM ('default', 'dev', 'preview', 'temp');
        
        DROP TYPE IF EXISTS blog_hosting_at;
        CREATE TYPE blog_hosting_at AS ENUM ('subdomain', 'domain', 'self');
        
        CREATE TABLE blogs (
            id serial PRIMARY KEY,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP,
            
            ip inet,
            
            is_blocked boolean DEFAULT false NOT NULL,
            blocked_at timestamp,
            
            hyvor_user_id bigint,
            theme_version_id bigint,
            
            subdomain citext UNIQUE NOT NULL,
            trial_ends_at timestamp NOT NULL,
            type blog_type DEFAULT 'default',
            
            hosting_at blog_hosting_at DEFAULT 'subdomain' NOT NULL,
            hosting_domain citext UNIQUE,
            hosting_url varchar(255),
            hosting_redirect_subdomain boolean DEFAULT true,
            
            meta jsonb,
            counts jsonb
        );
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
        Schema::dropIfExists('blogs');
    }
}
