"use client";
import { useRouter, usePathname } from "next/navigation";
import { logout } from "@/lib/api";

export default function Navbar() {
  const router = useRouter();
  const pathname = usePathname();

  const handleLogout = async () => {
    try {
      const token = localStorage.getItem("token");
      await logout(token);
    } catch (err) {
      console.error("Erreur lors de la déconnexion", err);
    } finally {
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      router.push("/login");
    }
  };

  const navItems = [
    { name: "Utilisateurs", path: "/admin/users" },
    { name: "Comptes Bancaires", path: "/admin/accounts" },
  ];

  return (
    <nav className="bg-white shadow-md">
      <div className="container mx-auto px-4">
        <div className="flex justify-between items-center h-16">
          <div className="flex items-center space-x-8">
            <h1 className="text-xl font-bold text-indigo-600">Bank Admin</h1>
            <div className="flex space-x-4">
              {navItems.map((item) => (
                <a
                  key={item.path}
                  href={item.path}
                  className={`px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                    pathname === item.path
                      ? "bg-indigo-100 text-indigo-700"
                      : "text-gray-700 hover:bg-gray-100"
                  }`}
                >
                  {item.name}
                </a>
              ))}
            </div>
          </div>

          <button
            onClick={handleLogout}
            className="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors text-sm font-medium"
          >
            Déconnexion
          </button>
        </div>
      </div>
    </nav>
  );
}