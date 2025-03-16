<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/styles/style.css">
</head>

<body>
    <div class="container">
        <nav>
            <img src="/img/close.svg" alt="close" id="has-menu">
            <h2 class="h2-like">Company</h2>
            <div class="line"></div>
            <ul class="main-navigation">
                <li class="list-navigation"><a href="/">Dashboard</a></li>
                <li class="list-navigation is-list-navigation"><a href="/profile">User profile</a></li>
                <li class="list-navigation"><a href="#">Maps</a></li>
                <li class="list-navigation"><a href="#">Notifications</a></li>
                <li class="list-navigation"><a href="#">Support</a></li>
            </ul>
        </nav>
        <div class="box-content">
            <img src="/img/menu.svg" alt="menu" id="is-menu">
            <h2 class="h2-like">User profile</h2>
            <div class="box-profile">
                <div class="card input-card">
                    <div class="box-form-img">
                        <p class="p-important">Edit Profile</p>
                        <p class="p-important">Complete your profile</p>
                    </div>
                    <form>
                        <div class="str-form">
                            <div class="col-form">
                                <label class="p-important" for="company">Company</label>
                                <input type="text" id="company">
                            </div>
                            <div class="col-form">
                                <label class="p-important" for="username">Username</label>
                                <input type="text" id="username">
                            </div>
                            <div class="col-form">
                                <label class="p-important" for="email">Email address</label>
                                <input type="email" id="email">
                            </div>
                        </div>
                        <div class="str-form">
                            <div class="col-form">
                                <label class="p-important" for="firstname">Firstname</label>
                                <input type="text" id="firstname">
                            </div>
                            <div class="col-form">
                                <label class="p-important" for="lastname">Lastname</label>
                                <input type="text" id="lastname">
                            </div>
                        </div>
                        <div class="str-form">
                            <div class="col-form">
                                <label class="p-important" for="address">Address</label>
                                <input type="text" id="address">
                            </div>
                        </div>
                        <div class="str-form">
                            <div class="col-form">
                                <label class="p-important" for="city">City</label>
                                <input type="text" id="city">
                            </div>
                            <div class="col-form">
                                <label class="p-important" for="country">Country</label>
                                <input type="text" id="country">
                            </div>
                            <div class="col-form">
                                <label class="p-important" for="postalCode">Postal code</label>
                                <input type="email" id="postalCode">
                            </div>
                        </div>
                        <div class="str-form">
                            <div class="col-form">
                                <label class="p-important" for="aboutMe">About me</label>
                                <textarea name="aboutMe" id="aboutMe"><?= 'Result: ' . $result ?></textarea>
                            </div>
                        </div>
                        <div class="str-form">
                            <div class="col-form button-form">
                                <input type="submit" value="update profile">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card about-card">
                    <img src="/img/lotus.jpg" alt="foto">
                    <div class="box-about-content">
                        <p class="p-unimportant">ceo / co-founder</p>
                        <h3 class="h3-like">Lorem ipsum</h3>
                        <p class="p-important">Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim esse
                            temporibus labore quod ad iste
                            facere fugit asperiores.
                        </p>
                    </div>
                </div>
            </div>
            <footer>
                <ul>
                    <li>About</li>
                    <li>Blog</li>
                    <li>Licenses</li>
                </ul>
                <p class="p-unimportant">2025</p>
            </footer>
        </div>
    </div>

    <script>
        document.querySelector('#is-menu').addEventListener('click', () => {
            document.querySelector('nav').classList.toggle('is-navigation');
        });
        document.querySelector('#has-menu').addEventListener('click', () => {
            document.querySelector('nav').classList.toggle('is-navigation');
        });
    </script>
</body>

</html>