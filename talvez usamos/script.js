//USUÁRIO ATUAL
let usuario = JSON.parse(localStorage.getItem("usuarioLogado"));

//CADASTRO
function cadastrar() {
  const nome = document.getElementById("nome").value;
  const email = document.getElementById("email").value;
  const senha = document.getElementById("senha").value;
  const tipo = document.getElementById("tipo").value;

  let usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];

  if (usuarios.find(u => u.email === email)) {
    alert("Email já cadastrado!");
    return;
  }

  const novoUsuario = {
    id: Date.now(),
    nome,
    email,
    senha,
    tipo
  };

  usuarios.push(novoUsuario);
  localStorage.setItem("usuarios", JSON.stringify(usuarios));

  alert("Cadastro realizado!");
  window.location.href = "login.html";
}

//LOGIN
function login() {
  const email = document.getElementById("email").value;
  const senha = document.getElementById("senha").value;

  let usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];

  const user = usuarios.find(
    u => u.email === email && u.senha === senha
  );

  if (!user) {
    alert("Login inválido");
    return;
  }

  localStorage.setItem("usuarioLogado", JSON.stringify(user));
  window.location.href = "dashboard.html";
}

//LOGOUT
function logout() {
  localStorage.removeItem("usuarioLogado");
  window.location.href = "index.html";
}

//CRIAR VAGA
function criarVaga() {
  if (!usuario) return;

  const titulo = document.getElementById("titulo").value;
  const descricao = document.getElementById("descricao").value;

  let vagas = JSON.parse(localStorage.getItem("vagas")) || [];

  vagas.push({
    id: Date.now(),
    titulo,
    descricao,
    empresa: usuario.nome,
    usuario_id: usuario.id
  });

  localStorage.setItem("vagas", JSON.stringify(vagas));

  carregarVagas();
}

//LISTAR VAGAS
function carregarVagas() {
  const lista = document.getElementById("listaVagas");
  if (!lista) return;

  let vagas = JSON.parse(localStorage.getItem("vagas")) || [];

  lista.innerHTML = "";

  vagas.forEach(vaga => {
    lista.innerHTML += `
      <div class="vaga-card">
        <h4>${vaga.titulo}</h4>
        <p><strong>${vaga.empresa}</strong></p>
        <p>${vaga.descricao}</p>
      </div>
    `;
  });
}

//DARK MODE
function toggleDarkMode() {
  document.body.classList.toggle("dark");
}

window.onload = () => {
  if (usuario) {
    const area = document.getElementById("areaEmpresa");
    if (area && usuario.tipo === "empresa") {
      area.style.display = "block";
    }
  }

  carregarVagas();
};