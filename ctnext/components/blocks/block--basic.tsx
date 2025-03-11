import { DrupalBlock } from "next-drupal"
import Image from "next/image"
import {absoluteUrl} from "@/lib/utils";

interface BlockBasicProps {
  block: DrupalBlock
}

export function BlockBasic({ block }: BlockBasicProps) {
  return (
    <div className="relative lg:max-h-[550px] overflow-hidden">
      {block.field_image && (
        <figure>
          <Image
            src={absoluteUrl(block.field_image.uri.url)}
            width={768}
            height={400}
            alt={block.field_image.resourceIdObjMeta.alt || ""}
            priority
          />
          {block.field_image.resourceIdObjMeta.title && (
            <figcaption className="py-2 text-sm text-center text-gray-600">
              {block.field_image.resourceIdObjMeta.title}
            </figcaption>
          )}
        </figure>
      )}
      <div className="container inset-0 z-10 flex items-center lg:absolute">
        <div className="top-0 flex flex-col items-start space-y-4 lg:max-w-[40%] text-text px-0 py-6 lg:px-6 lg:text-white lg:border border-text lg:bg-black/40">
          <p className="font-serif text-[28px] leading-tight">
            {block.field_title}
          </p>
          {block.body && (
            // Not secure way.
            <p className="text-[19px] leading-snug" dangerouslySetInnerHTML={{__html: block.body.value}}></p>
          )}
        </div>
      </div>
    </div>
  )
}
