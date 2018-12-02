<?php
class SiteResultsProvider{
    private $con;
    public function __construct($con){
        $this->con = $con;
    }
    public function getNumResults($term){
/*
	$query = $this->con->prepare("SELECT COUNT(*) as total from sites 
                                        WHERE title LIKE :term
                                        OR url LIKE :term
                                        OR description LIKE :term
                                        OR keywords LIKE :term 
                                        ");
*/								
		// Updated Query String according to change db structure
        $query = $this->con->prepare("SELECT COUNT(*) as total from search_index 
                                        WHERE url LIKE :term OR meta_title LIKE :term OR meta_desc LIKE :term OR meta_author LIKE :term OR meta_og_title LIKE :term OR meta_og_desc LIKE :term
                                        ");
        $searchTerm = "%" . $term . "%";
        $query->bindParam(":term", $searchTerm);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row["total"];
    }
    public function getResultsHtml($page, $pageSize, $term) {
        $fromLimit = ($page - 1) * $pageSize;
        //ex: $pageSize = 20
        //page 1 : ( 1 - 1 ) * 20 = 0
        //page 2 : ( 2 - 1 ) * 20 = 20
        //page 3 : ( 3 - 1 ) * 20 = 40
/*
        $query = $this->con->prepare("SELECT * 
										 FROM sites WHERE title LIKE :term 
										 OR url LIKE :term 
										 OR keywords LIKE :term 
										 OR description LIKE :term
										 ORDER BY clicks DESC
										 LIMIT :fromLimit, :pageSize 
										 ");
*/										 
		// Updated Query String according to change db structure										 
        $query = $this->con->prepare("SELECT * 
										 FROM search_index WHERE url LIKE :term OR meta_title LIKE :term OR meta_desc LIKE :term OR meta_author LIKE :term OR meta_og_title LIKE :term OR meta_og_desc LIKE :term 
										 LIMIT :fromLimit, :pageSize 
										 ");

										 $searchTerm = "%". $term . "%";
        $query->bindParam(":term", $searchTerm);
        $query->bindParam(":fromLimit", $fromLimit, PDO::PARAM_INT);
        $query->bindParam(":pageSize", $pageSize, PDO::PARAM_INT);
        $query->execute();
        $resultsHtml = "<div class='siteResults'>";

        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $id = $row["id"];
            $url = $row["url"];
			$meta_title = (isset($row["meta_title"]) && $row["meta_title"]!=""? $row["meta_title"]:$row["meta_og_title"]);
			$meta_description = (isset($row["meta_desc"]) && $row["meta_desc"]!=""? $row["meta_desc"]:$row["meta_og_desc"]);
			$site_name = $row["meta_og_site_name"];
			$last_date_crawled = date("jS F, Y", strtotime($row["last_date_crawled"]));
			/*
				Commenting out unsed variable in current code starting here
			*/
            // $title = $row["title"];
            // $description = $row["description"];

            // $title = $this->trimField($title, 120);
            // $description = $this->trimField($description, 230);
			/*
			$resultsHtml .= "<div class='resultContainer'>
					<h3 class='title'>
						<a class='result' href='$url' data-linkId='$id'>
							$title
						</a>
					</h3>
					<span class='url'>$url</span>
					<span class='description'>$description</span>
				</div>";
			*/	
			/*
				Commenting out unsed variable in current code ending here
			*/

			// Updated resultsHtml output according to response.
			/*$resultsHtml .= "<div class='resultContainer'>
								<h3 class='title'>
									<a class='result' href='$url' data-linkId='$id'>
										<span class='url'>$url</span>
									</a>
								</h3>
							</div>";*/
			$resultsHtml .= "<div class='r-row' style='margin-bottom: 20px;'>
								<div class='r-title'>
									<h3 style='font-size:20px;margin-bottom: 0; margin-top: 0;'><a href='{$url}' class='result' data-linkId='{$id}'>{$meta_title} - {$site_name}</a></h3>
								</div><!-- End r-title -->
								<div class='r-link'>
									<cite style='font-style:normal; color:#006d21;' class='url'>{$url}</cite>
								</div><!-- End r-link -->
								<div class='r-summary'>
									".(isset($meta_description) && $meta_description !=""? "<p><span>{$last_date_crawled} </span> - {$meta_description}</p>":"")."
								</div><!-- End r-summary -->
							</div><!-- End r-row -->";
							

        }
        $resultsHtml .= "</div>";

        return $resultsHtml;
    }
    private function trimField($string, $characterLimit){
        $dots = strlen($string) > $characterLimit ? "..." : "";
        return substr($string, 0, $characterLimit) . $dots;
    }
}