import { drupal } from "@/lib/drupal"
import type { Metadata } from "next"
import Link from "next/link"
export const metadata: Metadata = {
  description: "A Next.js site powered by a Drupal backend.",
}

export default async function BasicBlocks() {
  const products = await drupal.getResourceCollection("commerce_product--default", {
    params: {
      "fields[commerce_product]": "path",
    },
  })
  console.log(products)

  return (
    <>
      <h1 className="mb-10 text-6xl font-black">Products.</h1>
      {products?.length ? (
        products.map((product) => {
          console.log(product)
          return (
            <div key={product.id}>
              <Link href={product.path.alias}>
                {product.title}
              </Link>
              <hr className="my-20" />
            </div>
          )
        })
      ) : (
        <p className="py-4">No products found</p>
      )}
    </>
  )
}
