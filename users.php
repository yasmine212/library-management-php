
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

function ajouterUtilisateur($nom, $prenom, $email) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM utilisateurs WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        return false; // Email already exists
    }
    $sql = "INSERT INTO utilisateurs (nom, prenom, email) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nom, $prenom, $email]);
}

function modifierUtilisateur($id, $nom, $prenom, $email) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM utilisateurs WHERE email = ? AND id != ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $id]);
    if ($stmt->fetchColumn() > 0) {
        return false; // Email already exists
    }
    $sql = "UPDATE utilisateurs SET nom = ?, prenom = ?, email = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nom, $prenom, $email, $id]);
}

function supprimerUtilisateur($id) {
    global $pdo;
    $sql = "DELETE FROM utilisateurs WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}

function listerUtilisateurs() {
    global $pdo;
    $sql = "SELECT * FROM utilisateurs ORDER BY nom";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function rechercherUtilisateurParNom($nom) {
    global $pdo;
    $sql = "SELECT * FROM utilisateurs WHERE nom LIKE ? ORDER BY nom";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['%' . $nom . '%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$message = "";
$edit_mode = false;
$edit_user = null;

if (!empty($_POST)) {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? 0;
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($action === 'ajouter') {
        if (ajouterUtilisateur($nom, $prenom, $email)) {
            header("Location: ".$_SERVER['PHP_SELF']."?message=Utilisateur ajouté avec succès!");
            exit;
        } else {
            $message = "Erreur lors de l'ajout : Email déjà utilisé.";
        }
    } elseif ($action === 'modifier') {
        if (modifierUtilisateur($id, $nom, $prenom, $email)) {
            header("Location: ".$_SERVER['PHP_SELF']."?message=Utilisateur modifié avec succès!");
            exit;
        } else {
            $message = "Erreur lors de la modification : Email déjà utilisé.";
        }
    } elseif ($action === 'supprimer') {
        if (supprimerUtilisateur($id)) {
            header("Location: ".$_SERVER['PHP_SELF']."?message=Utilisateur supprimé avec succès!");
            exit;
        } else {
            $message = "Erreur lors de la suppression.";
        }
    } elseif ($action === 'edit_form') {
        $edit_mode = true;
        $sql = "SELECT * FROM utilisateurs WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$utilisateurs = (isset($_GET['recherche_nom']) && !empty($_GET['recherche_nom']))
    ? rechercherUtilisateurParNom($_GET['recherche_nom'])
    : listerUtilisateurs();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibliora - Gestion des Utilisateurs</title>
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
                <li><a href="index.php">Livres</a></li>
                <li><a href="users.php" class="active">Utilisateurs</a></li>
            </ul>
        </nav>
        <div class="search-form">
            <img src="search.jpg" alt="Icône de recherche" class="search-icon">
            <form action="" method="GET">
                <input type="text" name="recherche_nom" placeholder="Rechercher par nom" value="<?php echo isset($_GET['recherche_nom']) ? htmlspecialchars($_GET['recherche_nom']) : ''; ?>">
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
            <h2><?php echo $edit_mode ? 'Modifier un utilisateur' : 'Ajouter un utilisateur'; ?></h2>
            <p class="slogan">"Un nouvel utilisateur, une nouvelle aventure à écrire."</p>
            <form action="" method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'modifier' : 'ajouter'; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_user['id']); ?>">
                <?php endif; ?>
                <div class="input-box">
                    <input type="text" id="nom" name="nom" placeholder=" " value="<?php echo $edit_mode ? htmlspecialchars($edit_user['nom']) : ''; ?>" required>
                    <label for="nom">Nom</label>
                </div>
                <div class="input-box">
                    <input type="text" id="prenom" name="prenom" placeholder=" " value="<?php echo $edit_mode ? htmlspecialchars($edit_user['prenom']) : ''; ?>" required>
                    <label for="prenom">Prénom</label>
                </div>
                <div class="input-box">
                    <input type="email" id="email" name="email" placeholder=" " value="<?php echo $edit_mode ? htmlspecialchars($edit_user['email']) : ''; ?>" required>
                    <label for="email">Email</label>
                </div>
                <button type="submit" class="btn" aria-label="<?php echo $edit_mode ? 'Modifier l’utilisateur' : 'Ajouter un utilisateur'; ?>">
                    <?php echo $edit_mode ? 'Modifier' : 'Ajouter'; ?>
                </button>
            </form>
        </div>
        <button class="btn" onclick="openList()" aria-label="Afficher la liste des utilisateurs">Liste des Utilisateurs</button>
        <div id="modal" class="modal">
            <div id="book-list" class="book-list">
                <span class="close-btn" onclick="closeList()" aria-label="Fermer la liste">×</span>
                <h2>Liste des Utilisateurs</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilisateurs as $utilisateur): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($utilisateur['nom']); ?></td>
                                <td><?php echo htmlspecialchars($utilisateur['prenom']); ?></td>
                                <td><?php echo htmlspecialchars($utilisateur['email']); ?></td>
                                <td class="actions">
                                    <form action="" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="edit_form">
                                        <input type="hidden" name="id" value="<?php echo $utilisateur['id']; ?>">
                                        <button type="submit" class="edit-btn" aria-label="Modifier l’utilisateur">Modifier</button>
                                    </form>
                                    <form action="" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="id" value="<?php echo $utilisateur['id']; ?>">
                                        <button type="submit" class="delete-btn" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');" aria-label="Supprimer l’utilisateur">
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