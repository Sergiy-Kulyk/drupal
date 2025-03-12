import { Link } from "@/components/navigation/Link"
import { Logo } from "@/components/blocks/logo";

export function HeaderNav() {
  return (
    <header>
      <div className="container flex items-center justify-between py-6 mx-auto">
        <div className="logo">
          <Logo className="w-48 h-12 text-primary lg:h-16 lg:w-52" />
        </div>
        <Link href="/" className="text-2xl font-semibold no-underline">
          Next.js for Drupal
        </Link>
        <Link
          href="https://next-drupal.org/docs"
          target="_blank"
          rel="external"
          className="hover:text-blue-600"
        >
          Read the docs
        </Link>
        <Link
          href="/blocks"
          target="_self"
          className="hover:text-blue-600"
        >
          Basic blocks page
        </Link>
      </div>
    </header>
  )
}
