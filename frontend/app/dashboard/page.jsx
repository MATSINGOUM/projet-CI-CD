"use client";

import { useState } from "react";
import { login } from "@/lib/api";
import { useRouter } from "next/navigation";

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const router = useRouter();

  async function handleLogin(e) {
    e.preventDefault();
    const data = await login(email, password);
    localStorage.setItem("token", data.token);
    router.push("/dashboard/users");
  }

  return (
    <div className="container">
      <h1>Admin Login</h1>

      <form onSubmit={handleLogin} className="card">
        <input placeholder="Email" onChange={e => setEmail(e.target.value)} />
        <input type="password" placeholder="Password" onChange={e => setPassword(e.target.value)} />
        <button>Login</button>
      </form>
    </div>
  );
}
