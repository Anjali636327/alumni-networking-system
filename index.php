<?php 
include("includes/header.php");

if(isset($_POST['post'])){

	$uploadOk = 1;
	$imageName = $_FILES['fileToUpload']['name'];
	$errorMessage = "";

	if($imageName != "") {
		$targetDir = "assets/images/posts/";
		$imageName = $targetDir . uniqid() . basename($imageName);
		$imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

		if($_FILES['fileToUpload']['size'] > 10000000) {
			$errorMessage = "Sorry your file is too large";
			$uploadOk = 0;
		}

		if(strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg") {
			$errorMessage = "Sorry, only jpeg, jpg and png files are allowed";
			$uploadOk = 0;
		}

		if($uploadOk) {
			if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
				//image uploaded okay
			} else {
				$uploadOk = 0;
			}
		}
	}

	if($uploadOk) {
		$post = new Post($con, $userLoggedIn);
		$post->submitPost($_POST['post_text'], 'none', $imageName);
	} else {
		echo "<div style='text-align:center;' class='alert alert-danger'>
				$errorMessage
			</div>";
	}

}
?>

<div class="user_details column">
	<a href="<?php echo $userLoggedIn; ?>">  <img src="<?php echo $user['profile_pic']; ?>"> </a>

	<div class="user_details_left_right">
		<a href="<?php echo $userLoggedIn; ?>">
		<?php 
		echo $user['first_name'] . " " . $user['last_name'];
		?>
		</a>
		<br>
		<?php 
		echo "Posts: " . $user['num_posts']. "<br>"; 
		echo "Likes: " . $user['num_likes'];
		?>
	</div>
</div>

<div class="main_column column">
	<form class="post_form" action="index.php" method="POST" enctype="multipart/form-data">
		<input type="file" name="fileToUpload" id="fileToUpload">
		<textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
		<input type="submit" name="post" id="post_button" value="Post">
		<hr>
	</form>

	<div class="posts_area"></div>
	<img id="loading" src="assets/images/icons/loading.gif">
</div>

<div class="user_details column">
	<h4>Popular</h4>
	<div class="trends">
		<?php 
		$query = mysqli_query($con, "SELECT * FROM trends ORDER BY hits DESC LIMIT 9");
		if (is_array($query)) {
			foreach ($query as $row) {
				$word = $row['title'];
				$word_dot = strlen($word) >= 14 ? "..." : "";
				$trimmed_word = str_split($word, 14);
				$trimmed_word = $trimmed_word[0];

				echo "<div style'padding: 1px'>";
				echo $trimmed_word . $word_dot;
				echo "<br></div><br>";
			}
		}
		?>
	</div>
</div>

<script>
$(document).ready(function() {
	var userLoggedIn = '<?php echo $userLoggedIn; ?>';
	var inProgress = false;

	loadPosts(); 

	$(window).scroll(function() {
		var bottomElement = $(".status_post").last();
		var noMorePosts = $('.posts_area').find('.noMorePosts').val();

		if (isElementInView(bottomElement[0]) && noMorePosts == 'false') {
			loadPosts();
		}
	});

	function loadPosts() {
		if(inProgress) { 
			return;
		}

		inProgress = true;
		$('#loading').show();

		var page = $('.posts_area').find('.nextPage').val() || 1;

		$.ajax({
			url: "includes/handlers/ajax_load_posts.php",
			type: "POST",
			data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
			cache:false,

			success: function(response) {
				$('.posts_area').find('.nextPage').remove(); 
				$('.posts_area').find('.noMorePosts').remove(); 
				$('.posts_area').find('.noMorePostsText').remove(); 

				$('#loading').hide();
				$(".posts_area").append(response);

				inProgress = false;
			}
		});
	}

	function isElementInView (el) {
		var rect = el.getBoundingClientRect();

		return (
			rect.top >= 0 &&
			rect.left >= 0 &&
			rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && 
			rect.right <= (window.innerWidth || document.documentElement.clientWidth)
		);
	}
});
</script>

<!-- Chatbot Integration -->
<div id="chatbot" style="position:fixed; bottom:0; right:20px; width:300px; height:500px;">
</div>

<script>
window.embeddedChatbotConfig = {
	chatbotId: "wBVa_rmeNicgAUR48oOwm",
	domain: "www.chatbase.co"
}
</script>

<script
src="https://www.chatbase.co/embed.min.js"
chatbotId="wBVa_rmeNicgAUR48oOwm"
domain="www.chatbase.co"
defer>
</script>

</body>
</html>
