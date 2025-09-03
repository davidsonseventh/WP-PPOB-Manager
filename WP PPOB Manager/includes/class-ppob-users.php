<?php
defined( 'ABSPATH' ) || exit;

class WPPPOB_Users {
    public function __construct() {
        add_action( 'show_user_profile', [ $this, 'add_balance_field' ] );
        add_action( 'edit_user_profile', [ $this, 'add_balance_field' ] );
        add_action( 'personal_options_update', [ $this, 'save_balance_field' ] );
        add_action( 'edit_user_profile_update', [ $this, 'save_balance_field' ] );
    }

    public function add_balance_field( $user ) {
        $balance = WPPPOB_Balances::get( $user->ID );
        ?>
        <h3><?php _e( 'PPOB Balance', 'wp-ppob' ); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="wppob_balance"><?php _e( 'Saldo', 'wp-ppob' ); ?></label></th>
                <td>
                    <input type="number" step="0.01" name="wppob_balance" id="wppob_balance" value="<?php echo esc_attr( $balance ); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_balance_field( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) return false;
        if ( isset( $_POST['wppob_balance'] ) ) {
            WPPPOB_Balances::add( $user_id, floatval( $_POST['wppob_balance'] ) - WPPPOB_Balances::get( $user_id ) );
        }
    }
}