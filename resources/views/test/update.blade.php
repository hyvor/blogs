<!-- This blade file was created for testing purposes. -->

<form action="" method="POST">
    @csrf 

    <input type="hidden" name = "id" value ={{$getData['id']}} placeholder="enter the blog_id"> <br><br>

    <input type="text" name = "blog_id" value ={{$getData['blog_id']}} placeholder="enter the blog_id"> <br><br>
    <input type="text" name = "old_url" value = {{$getData['old_url']}} placeholder="enter the old_url"> <br><br>
    <input type="text" name = "new_url" value = {{$getData['new_url']}} placeholder="enter the new_url"> <br><br>
    <input type="text" name = "type" value = {{$getData['type']}} placeholder="enter the type"> <br><br>

    <button type="submit">update</button>
</form> 

<br><br>

