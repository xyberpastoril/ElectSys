<?php
session_start();
// localize variables
$position = $_GET['position'];
$skipAhead = $_SESSION['skipAhead'];
if(!empty($_SESSION['lrn']))
{
    if($_GET['value'] == 0 || $_GET['value'] == NULL)
    {
        $_SESSION['candidates'][$position] = 0;
    }
    else
    {
        if($position >= 7 && $position <= 20)
        {
            if($position % 2 == 1 && $_SESSION['candidates'][$position+1] == $_GET['value']) // odd - 7,9,11,13,15 (first)
            {
                $_SESSION['candidates'][$position] = 0;
                header("Location: vote.php?position=".$position."&error=duplicatevote");
                exit();
            }
            else if ($position % 2 == 0 && $_SESSION['candidates'][$position-1] == $_GET['value']) // even - 8,10,12,14,16 (second)
            {
                $_SESSION['candidates'][$position] = 0;
                header("Location: vote.php?position=".$position."&error=duplicatevote");
                exit();
            }
            else
            {
                $_SESSION['candidates'][$position] = $_GET['value'];
            }
        }
        else if ($position > 0 && $position < 7)
        {
            $_SESSION['candidates'][$position] = $_GET['value'];
        } 
    }
    /*if(($position > 0 && $position < 9) || $position == 9 || $position == 11 || $position == 13 || $position == 15 || $position == 17 || $position == 19)
        {
            if($skipAhead == 1)
            { // redirects to review.php if skipAhead is true (up once reached review at least once)
                header("Location: review.php");
                exit();
            }
            else 
            { // if not true, continue with actual flow
                $position++;
            }
        }
        else if ($position == 10 || $position == 12 || $position == 14 || $position == 16 || $position == 18 || $position == 20)
        { // if positions are equal to these, which marks the end of voting for specific user, redirects to review.php
            header("Location: review.php");
            exit();
        }

        if($skipAhead == 1)
        { // POSSIBLE DUPLICATE CODE FROM LINE 38, GIVE IT A CHECK
            header("Location: review.php");
            exit();
        }
        else
        {
            header("Location: vote.php?position=".$position);
            exit();
        }*/
    if($skipAhead == 1)
    {
        header("Location: review.php");
        exit();
    }
    else 
    { // if not true, continue with actual flow
        $position++;
        header("Location: vote.php?position=".$position);
        exit();
    }

}
else
{
    header("Location: index.php?error=NoSession");
    exit();
}