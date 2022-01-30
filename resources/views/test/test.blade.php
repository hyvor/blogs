<!-- This blade file was created for testing purposes. -->

<form action="" method="POST">
    @csrf 
    <!-- <input type="text" name = "blog_id" placeholder="enter the blog_id"> <br><br> -->
    <input type="text" name = "old_url" placeholder="enter the old_url"> <br><br>
    <input type="text" name = "new_url" placeholder="enter the new_url"> <br><br>
    <input type="text" name = "type" placeholder="enter the type"> <br><br>

    <button type="submit">Add</button>
</form> 

<br><br><br>

<table border="1">
    <tr>
        <td>blog_id</td>
        <td>old_url</td>
        <td>new_url</td>
        <td>type</td>
    </tr>
    @foreach($getData as $i)
    <tr>
        <td>{{$i['blog_id']}}</td>
        <td>{{$i['old_url']}}</td>
        <td>{{$i['new_url']}}</td>
        <td>{{$i['type']}}</td>
        <!-- <td>
            <form method="DELETE">
                @method('DELETE')
                @csrf
                <a href ="{{'/api/console/v0/blog/supun/deleteRedirect/'.$i['id']}}">delete</a>
            </form>
        </td> -->

        <td><a href ="{{'/api/console/v0/blog/supun/redirect/'.$i['id']}}">delete</a></td>
        <td><a href = "{{'/api/console/v0/blog/supun/updateRedirect/'.$i['id']}}">update</a></td>

        <!-- <td><a href="redirect/"$i['id']>delete</a></td> -->
    </tr>
   <!-- <li>{{$i}}<li> -->
    @endforeach
<table>
    
<br><br><br>

<a href = "/api/console/v0/blog/supun/test">update</a>

<br><br><br>


<ul>
@foreach($getData as $i)
   <li>{{$i}}<li>
@endforeach
</ul>