<?php

function get_user_membership_id()
{
    return 1;
}

function get_user_max_products($user_id = 0)
{
    wc_memberships_get_membership_plan();
    return 1;
}
