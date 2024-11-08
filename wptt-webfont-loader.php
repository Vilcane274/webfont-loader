class StylesheetHandler {
    private $remote_url;
    private $local_path;
    private $css;
    
    // Konstruktor, kas iestata attālināto URL un vietējo ceļu
    public function __construct( $remote_url, $local_path ) {
        $this->remote_url = $remote_url;
        $this->local_path = $local_path;
        $this->css = null;
    }

    // Atgriež vietējo CSS faila ceļu
    public function get_local_stylesheet_path() {
        return $this->local_path . '/style.css';
    }

    // Pārbauda, vai vietējais fails eksistē
    public function local_file_exists() {
        return file_exists( $this->get_local_stylesheet_path() );
    }

    // Atgriež pilnu URL, ņemot vērā attālināto URL
    protected function get_absolute_path( $url ) {
        if ( 0 === stripos( $url, '/' ) ) {
            $parsed_url = parse_url( $this->remote_url );
            return $parsed_url['scheme'] . '://' . $parsed_url['host'] . $url;
        }
        return $url;
    }

    // Saņem failu sistēmas objektu
    protected function get_filesystem() {
        global $wp_filesystem;

        // Ja filesystem objekts vēl nav inicializēts
        if ( ! $wp_filesystem ) {
            if ( ! function_exists( 'WP_Filesystem' ) ) {
                require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
            }
            WP_Filesystem();
        }
        return $wp_filesystem;
    }

    // Lejupielādē CSS failu no attālinātā URL
    public function download_stylesheet() {
        $remote_url = $this->get_absolute_path( 'style.css' ); // Pieņemam, ka faila nosaukums ir 'style.css'
        $response = wp_remote_get( $remote_url );

        if ( is_wp_error( $response ) ) {
            return false; // Ja radās kļūda
        }

        $body = wp_remote_retrieve_body( $response );
        if ( empty( $body ) ) {
            return false; // Ja saņemts tukšs saturs
        }

        $this->css = $body;
        return true;
    }

    // Iegūst CSS, ja tas vēl nav ielādēts
    public function get_styles() {
        if ( null === $this->css ) {
            $this->download_stylesheet();
        }
        return $this->css;
    }

    // Raksta CSS saturu vietējā failā
    protected function write_stylesheet() {
        $file_path = $this->get_local_stylesheet_path();
        $filesystem = $this->get_filesystem();

        // Ja fails vēl nav radīts, mēģinām to izveidot
        if ( ! $filesystem->exists( $file_path ) && ! $filesystem->touch( $file_path ) ) {
            return false; // Ja fails nevar tikt radīts
        }

        // Rakstām CSS saturu failā
        if ( null === $this->css ) {
            $this->get_styles();  // Ja CSS vēl nav ielādēts, iegūstam to
        }

        if ( ! $filesystem->put_contents( $file_path, $this->css ) ) {
            return false; // Ja rakstīšana neizdevās
        }

        return $file_path; // Atgriežam radītā faila ceļu
    }

    // Sinhronizē vietējo failu ar attālināto
    public function sync_stylesheet() {
        // Ja vietējais fails jau eksistē un ir līdzvērtīgs attālinātajam, tad neko nedarām
        if ( $this->local_file_exists() ) {
            return true;
        }

        // Mēģinām iegūt CSS datus
        if ( ! $this->download_stylesheet() ) {
            return false; // Ja neizdevās lejupielādēt attālināto failu
        }

        // Mēģinām ierakstīt iegūto CSS failā
        return $this->write_stylesheet();
    }
}
