// app/products/[slug]/page.tsx
import { drupal } from "@/lib/drupal"
import { notFound } from "next/navigation"

interface Props {
  params: { slug: string }
}

export async function generateStaticParams() {
  const products = await drupal.getResourceCollection("commerce_product--default", {
    params: {
      "fields[commerce_product]": "path",
    },
  })
  console.log(products)
  return products.map((product: any) => {
    // Наприклад: /products/t-shirt → slug = t-shirt
    const slug = product.path.alias.replace("/products/", "")
    return { slug }
  })
}
export default async function ProductPage({ params }: Props) {
  const product = await drupal.getResourceByPath(`/products/${params.slug}`, {
    params: {
      include: "variations",
    },
  })

  if (!product) {
    notFound()
  }

  return (
    <div>
      <h1>{product.title}</h1>
      <p>
        {product.variations?.[0]?.price?.number}{" "}
        {product.variations?.[0]?.price?.currency_code}
      </p>
      {product.variations?.[0]?.field_image?.[0]?.url && (
        <img
          src={product.variations[0].field_image[0].url}
          alt={product.title}
          width={300}
        />
      )}
    </div>
  )
}
