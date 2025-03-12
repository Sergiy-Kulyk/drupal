import { BlockBasic } from "@/components/blocks/block--basic"
import { drupal } from "@/lib/drupal"
import type { Metadata } from "next"
import type {DrupalBlock} from "next-drupal"

export const metadata: Metadata = {
  description: "A Next.js site powered by a Drupal backend.",
}

export default async function Home() {
  const blocks = await drupal.getResourceCollection<DrupalBlock[]>(
    "block_content--basic",
    {
      params: {
        "filter[status]": 1,
        "fields[block_content--basic]": "field_title,body,field_image",
        include: "field_image",
      },
      next: {
        revalidate: 0,
      },
    }
  )

  console.log(blocks)

  return (
    <>
      <h1 className="mb-10 text-6xl font-black">Basic Blocks.</h1>
      {blocks?.length ? (
        blocks.map((block: DrupalBlock) => {
          console.log(block)
          return (
            <div key={block.id}>
              <BlockBasic block={block} />
              <hr className="my-20" />
            </div>
          )
        })
      ) : (
        <p className="py-4">No blocks found</p>
      )}
    </>
  )
}
