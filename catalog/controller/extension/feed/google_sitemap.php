<?php
class ControllerExtensionFeedGoogleSitemap extends Controller {
    
    private $languages = [];
    
    public function index() {
		if ($this->config->get('feed_google_sitemap_status')) {
            
            $this->languages = $this->db->query("SELECT * FROM " . DB_PREFIX . "language where status=1")->rows;
            
            $this->load->model('catalog/product');
            $this->load->model('catalog/information');
            $this->load->model('catalog/category');
			$this->load->model('catalog/manufacturer');
            
			$output  = '<?xml version="1.0" encoding="UTF-8"?>';
			$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';			

            foreach ($this->languages as $key => $languages) {
                
                $this->config->set('config_language_id', $languages['language_id']);
                $this->config->set('config_language', $languages['code']);
                
                $products = $this->model_catalog_product->getProducts();

                foreach ($products as $product) {
                    if ($product['image']) {
                        $output .= '<url>';
                        $output .= '  <loc>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</loc>';
                        $output .= '  <changefreq>weekly</changefreq>';
                        $output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])) . '</lastmod>';
                        $output .= '  <priority>1.0</priority>';
                        //$output .= '  <image:image>';
                        //$output .= '  <image:loc>' .  . '</image:loc>';
                        //$output .= '  <image:caption>' . $product['name'] . '</image:caption>';
                        //$output .= '  <image:title>' . $product['name'] . '</image:title>';
                        //$output .= '  </image:image>';
                        $output .= '</url>';
                    }
                }

                $output .= $this->getCategories(0);

                $manufacturers = $this->model_catalog_manufacturer->getManufacturers();

                foreach ($manufacturers as $manufacturer) {
                    $output .= '<url>';
                    $output .= '  <loc>' . $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']) . '</loc>';
                    $output .= '  <changefreq>weekly</changefreq>';
                    $output .= '  <priority>0.7</priority>';
                    $output .= '</url>';
                }



                $informations = $this->model_catalog_information->getInformations();

                foreach ($informations as $information) {
                    $output .= '<url>';
                    $output .= '  <loc>' . $this->url->link('information/information', 'information_id=' . $information['information_id']) . '</loc>';
                    $output .= '  <changefreq>weekly</changefreq>';
                    $output .= '  <priority>0.5</priority>';
                    $output .= '</url>';
                }

            }
            
			$output .= '</urlset>';

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);
		}
	}

	protected function getCategories($parent_id, $current_path = '') {
		$output = '';

		$results = $this->model_catalog_category->getCategories($parent_id);

		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}

			$output .= '<url>';
			$output .= '  <loc>' . $this->url->link('product/category', 'path=' . $new_path) . '</loc>';
			$output .= '  <changefreq>weekly</changefreq>';
			$output .= '  <priority>0.7</priority>';
			$output .= '</url>';

			$output .= $this->getCategories($result['category_id'], $new_path);
		}

		return $output;
	}
    
}
