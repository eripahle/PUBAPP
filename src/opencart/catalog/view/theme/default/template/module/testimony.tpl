<h3><?php echo $heading_title; ?></h3>
<style type="text/css">
    .float_l{float:left;}
    .float_r{float: right;}
    .clear{clear: both;}

    #list{
        width: 800px;
    }

    #list img{
        border: 2px solid #000;
        padding: 5px;
        margin-right: 10px;
    }
    .content{
        text-align: justify;
    }


    hr{
        width: 600px;
    }
</style>

<body>    
    <div id="list">
        <?php foreach ($testimonys as $testimony) { ?>		
        <p>
        <div class="float_l">
            <img src="image/gallery/event1.jpg" width="70px" />
        </div>
        <div class="content">
            <b>Author : <?php echo $testimony['author'];?></b><br>
            Testimony : <?php echo $testimony['text'];?><br>
        </div>
    </p>
    <div class="clear"></div>
    <hr>
    <?php } ?>
    <a href="<?php echo $viewAll; ?>" >View All Testimony</a>
    </div>
</body>
