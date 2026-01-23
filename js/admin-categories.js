// Add Category Form TODO backend
function showAddCategoryForm() {
    const form = document.getElementById('categoryForm');
    const formTitle = document.getElementById('formTitle');

    formTitle.textContent = 'Dodaj nową kategorię';
    form.style.display = 'block';

    document.getElementById('categoryName').value = '';
    document.getElementById('categorySlug').value = '';
    document.getElementById('categoryIcon').value = '';
    document.getElementById('categoryColor').value = '#e50914';
    document.getElementById('categoryDescription').value = '';

    form.scrollIntoView({ behavior: 'smooth' });
}

function hideCategoryForm() {
    const form = document.getElementById('categoryForm');
    form.style.display = 'none';
}

function editCategory(categoryId) {
    const form = document.getElementById('categoryForm');
    const formTitle = document.getElementById('formTitle');

    formTitle.textContent = 'Edytuj kategorię';
    form.style.display = 'block';

    console.log('Edytuj kategorię ID:', categoryId);

    form.scrollIntoView({ behavior: 'smooth' });
}

function deleteCategory(categoryId) {
    if (confirm('Czy na pewno chcesz usunąć tę kategorię?')) {
        console.log('Usuń kategorię ID:', categoryId);

        alert('Kategoria została usunięta!');
    }
}

function saveCategory(event) {
    event.preventDefault();

    const name = document.getElementById('categoryName').value;
    const slug = document.getElementById('categorySlug').value;
    const icon = document.getElementById('categoryIcon').value;
    const color = document.getElementById('categoryColor').value;
    const description = document.getElementById('categoryDescription').value;

    console.log('Zapisz kategorię:', { name, slug, icon, color, description });

    alert('Kategoria została zapisana!');
    hideCategoryForm();
}

const categoryNameInput = document.getElementById('categoryName');
const categorySlugInput = document.getElementById('categorySlug');

if (categoryNameInput && categorySlugInput) {
    categoryNameInput.addEventListener('input', function() {
        const slug = this.value
            .toLowerCase()
            .replace(/ą/g, 'a')
            .replace(/ć/g, 'c')
            .replace(/ę/g, 'e')
            .replace(/ł/g, 'l')
            .replace(/ń/g, 'n')
            .replace(/ó/g, 'o')
            .replace(/ś/g, 's')
            .replace(/ź|ż/g, 'z')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

        categorySlugInput.value = slug;
    });
}

