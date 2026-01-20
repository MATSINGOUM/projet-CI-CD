import "./globals.css";

export const metadata = {
  title: "Bank Admin Panel",
  description: "Gestion des utilisateurs et comptes bancaires",
};

export default function RootLayout({ children }) {
  return (
    <html lang="fr">
      <body>{children}</body>
    </html>
  );
}