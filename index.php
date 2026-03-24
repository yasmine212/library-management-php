
<?php
$host = 'localhost';
$dbname = 'bibliotheque';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function ajouterLivre($titre, $auteur, $annee, $genre) {
    global $pdo;
    $sql = "INSERT INTO livres (titre, auteur, annee, genre) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$titre, $auteur, $annee, $genre]);
}

function modifierLivre($id, $titre, $auteur, $annee, $genre) {
    global $pdo;
    $sql = "UPDATE livres SET titre = ?, auteur = ?, annee = ?, genre = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$titre, $auteur, $annee, $genre, $id]);
}

function supprimerLivre($id) {
    global $pdo;
    $sql = "DELETE FROM livres WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}

function listerLivres() {
    global $pdo;
    $sql = "SELECT * FROM livres ORDER BY titre";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function rechercherLivres($livre) {
    global $pdo;
    $sql = "SELECT * FROM livres WHERE titre LIKE ? OR auteur LIKE ? ORDER BY titre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['%'.$livre.'%', '%'.$livre.'%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$message = "";
$edit_mode = false;
$edit_book = null;

if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

if (!empty($_POST)) {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? 0;
    $titre = $_POST['titre'] ?? '';
    $auteur = $_POST['auteur'] ?? '';
    $annee = $_POST['annee'] ?? '';
    $genre = $_POST['genre'] ?? '';

    if ($action === 'ajouter') {
        if (ajouterLivre($titre, $auteur, $annee, $genre)) {
            header("Location: ".$_SERVER['PHP_SELF']."?message=Livre ajouté avec succès!");
            exit;
        } else {
            $message = "Erreur lors de l'ajout.";
        }
    } elseif ($action === 'modifier') {
        if (modifierLivre($id, $titre, $auteur, $annee, $genre)) {
            header("Location: ".$_SERVER['PHP_SELF']."?message=Livre modifié avec succès!");
            exit;
        } else {
            $message = "Erreur lors de la modification.";
        }
    } elseif ($action === 'supprimer') {
        if (supprimerLivre($id)) {
            header("Location: ".$_SERVER['PHP_SELF']."?message=Livre supprimé avec succès!");
            exit;
        } else {
            $message = "Erreur lors de la suppression.";
        }
    } elseif ($action === 'edit_form') {
        $edit_mode = true;
        $sql = "SELECT * FROM livres WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $edit_book = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$livres = (isset($_GET['recherche_livre']) && !empty($_GET['recherche_livre']))
    ? rechercherLivres($_GET['recherche_livre'])
    : listerLivres();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibliora - Gestion des Livres</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="branding">
            <img src="bibliora.png" alt="Logo Bibliora" class="logo">
           
        </div>
        <nav class="nav">
            <ul>
                <li><a href="home.php">Accueil</a></li>
                <li><a href="index.php" class="active">Livres</a></li>
                <li><a href="users.php">Utilisateurs</a></li>
            </ul>
        </nav>
        <div class="search-form">
            <img src="search.jpg" alt="Icône de recherche" class="search-icon">
            <form action="" method="GET">
                <input type="text" name="recherche_livre" placeholder="Rechercher par titre ou auteur" value="<?php echo isset($_GET['recherche_livre']) ? htmlspecialchars($_GET['recherche_livre']) : ''; ?>">
                <input type="submit" value="Rechercher">
            </form>
        </div>
    </header>
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'succès') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <div class="form-container">
            <h2><?php echo $edit_mode ? 'Modifier un livre' : 'Ajouter un livre'; ?></h2>
            <p class="slogan">"Un livre ajouté, une porte de plus vers l’imaginaire."</p>
            <form action="" method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'modifier' : 'ajouter'; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_book['id']); ?>">
                <?php endif; ?>
                <div class="input-box">
                    <input type="text" id="titre" name="titre" placeholder=" " value="<?php echo $edit_mode ? htmlspecialchars($edit_book['titre']) : ''; ?>" required>
                    <label for="titre">Titre du livre</label>
                </div>
                <div class="input-box">
                    <input type="text" id="auteur" name="auteur" placeholder=" " value="<?php echo $edit_mode ? htmlspecialchars($edit_book['auteur']) : ''; ?>" required>
                    <label for="auteur">Auteur</label>
                </div>
                <div class="input-box">
                    <input type="number" id="annee" name="annee" min="1900" max="2025" placeholder=" " value="<?php echo $edit_mode ? htmlspecialchars($edit_book['annee']) : ''; ?>" required>
                    <label for="annee">Année</label>
                </div>
                <div class="input-box">
                    <input type="text" id="genre" name="genre" placeholder=" " value="<?php echo $edit_mode ? htmlspecialchars($edit_book['genre']) : ''; ?>" required>
                    <label for="genre">Genre</label>
                </div>
                <button type="submit" class="btn" aria-label="<?php echo $edit_mode ? 'Modifier le livre' : 'Ajouter un livre'; ?>">
                    <?php echo $edit_mode ? 'Modifier' : 'Ajouter'; ?>
                </button>
            </form>
        </div>
       
        <button class="btn" onclick="openList()" aria-label="Afficher la liste des livres">Liste des Livres</button>
      
        <div id="modal" class="modal">
            <div id="book-list" class="book-list">
                <span class="close-btn" onclick="closeList()" aria-label="Fermer la liste">×</span>
                <h2>Liste des livres</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Année</th>
                            <th>Genre</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($livres as $livre): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($livre['titre']); ?></td>
                                <td><?php echo htmlspecialchars($livre['auteur']); ?></td>
                                <td><?php echo htmlspecialchars($livre['annee']); ?></td>
                                <td><?php echo htmlspecialchars($livre['genre']); ?></td>
                                <td class="actions">
                                    <form action="" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="edit_form">
                                        <input type="hidden" name="id" value="<?php echo $livre['id']; ?>">
                                        <button type="submit" class="edit-btn" aria-label="Modifier le livre">Modifier</button>
                                    </form>
                                    <form action="" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="id" value="<?php echo $livre['id']; ?>">
                                        <button type="submit" class="delete-btn" onclick="return confirm('Voulez-vous vraiment supprimer ce livre ?');" aria-label="Supprimer le livre">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <footer class="footer">
        <div class="footer-content">
            <p>© 2025 Bibliora. Tous droits réservés.</p>
        </div>
    </footer>
    <script src="biblio.js"></script>
</body>
</html>